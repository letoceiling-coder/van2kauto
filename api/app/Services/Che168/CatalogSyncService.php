<?php

namespace App\Services\Che168;

use App\Models\Brand;
use App\Models\Listing;
use App\Models\ListingPhoto;
use App\Models\ListingReport;
use App\Models\ScrapeCheckpoint;
use App\Models\Series;
use App\Models\Spec;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class CatalogSyncService
{
    public function __construct(
        private readonly ApiClient $api,
    ) {}

    public function syncBrands(): int
    {
        $payload = $this->api->brands();
        $count = 0;

        /** @var list<array<string, mixed>> $groups */
        $groups = $payload['result']['brands'] ?? [];

        foreach ($groups as $group) {
            foreach ($group['brand'] ?? [] as $item) {
                Brand::query()->updateOrCreate(
                    ['brand_id' => (int) $item['bid']],
                    [
                        'name' => trim((string) ($item['name'] ?? '')),
                        'py' => $item['py'] ?? null,
                        'icon_url' => $item['icon'] ?? null,
                    ],
                );
                $count++;
            }
        }

        return $count;
    }

    public function syncSeriesForBrand(int $brandId): int
    {
        $payload = $this->api->series($brandId);
        $count = 0;

        foreach ($payload['result']['list'] ?? [] as $block) {
            foreach ($block['item'] ?? [] as $item) {
                $seriesId = (int) ($item['id'] ?? $item['seriesid'] ?? 0);
                if ($seriesId <= 0) {
                    continue;
                }

                Series::query()->updateOrCreate(
                    ['series_id' => $seriesId],
                    [
                        'brand_id' => $brandId,
                        'name' => trim((string) ($item['name'] ?? '')),
                        'icon_url' => $item['icon'] ?? $item['img'] ?? null,
                    ],
                );
                $count++;
            }
        }

        return $count;
    }

    /**
     * @param  array<string, scalar|null>  $filters
     * @return array{saved: int, page: int, total: int, pagecount: int}
     */
    /**
     * @return array{inserted: int, skipped: int, consecutive_existing: int, page: int, pagecount: int}
     */
    public function syncSearchPageNewOnly(int $page, array $filters = [], int $staleBreak = 5): array
    {
        $payload = $this->api->search(array_merge($filters, ['pageindex' => $page]));
        $result = $payload['result'] ?? [];
        $inserted = 0;
        $skipped = 0;
        $consecutive = 0;

        foreach ($result['carlist'] ?? [] as $row) {
            $infoid = (int) ($row['infoid'] ?? 0);
            if ($infoid <= 0) {
                continue;
            }

            if (Listing::query()->whereKey($infoid)->exists()) {
                $skipped++;
                $consecutive++;
                if ($consecutive >= $staleBreak) {
                    break;
                }

                continue;
            }

            $consecutive = 0;
            $this->upsertFromSearchRow($row);
            $inserted++;
        }

        return [
            'inserted' => $inserted,
            'skipped' => $skipped,
            'consecutive_existing' => $consecutive,
            'page' => (int) ($result['pageindex'] ?? $page),
            'pagecount' => (int) ($result['pagecount'] ?? 0),
        ];
    }

    public function syncSearchPage(int $page, array $filters = []): array
    {
        $payload = $this->api->search(array_merge($filters, ['pageindex' => $page]));
        $result = $payload['result'] ?? [];
        $saved = 0;
        $infoids = [];

        foreach ($result['carlist'] ?? [] as $row) {
            $this->upsertFromSearchRow($row);
            $saved++;
            $infoids[] = (int) ($row['infoid'] ?? 0);
        }

        return [
            'saved' => $saved,
            'infoids' => array_values(array_filter($infoids)),
            'page' => (int) ($result['pageindex'] ?? $page),
            'total' => (int) ($result['totalcount'] ?? 0),
            'pagecount' => (int) ($result['pagecount'] ?? 0),
        ];
    }

    public function syncListingDetail(int $infoid): Listing
    {
        $payload = $this->api->carInfo($infoid);
        $row = $payload['result'] ?? [];

        return DB::transaction(function () use ($row, $infoid) {
            $brandId = isset($row['brandid']) ? (int) $row['brandid'] : null;
            $seriesId = isset($row['seriesid']) ? (int) $row['seriesid'] : null;
            $specId = isset($row['specid']) ? (int) $row['specid'] : null;

            if ($brandId) {
                Brand::query()->updateOrCreate(
                    ['brand_id' => $brandId],
                    ['name' => trim((string) ($row['brandname'] ?? 'Unknown'))],
                );
            }

            if ($seriesId && $brandId) {
                Series::query()->updateOrCreate(
                    ['series_id' => $seriesId],
                    [
                        'brand_id' => $brandId,
                        'name' => trim((string) ($row['seriesname'] ?? '')),
                    ],
                );
            }

            if ($specId && $seriesId) {
                Spec::query()->updateOrCreate(
                    ['spec_id' => $specId],
                    [
                        'series_id' => $seriesId,
                        'name' => trim((string) ($row['specname'] ?? '')),
                        'year_label' => $row['yearname'] ?? null,
                    ],
                );

                $this->syncSpecAttributes($specId);
            }

            $listing = Listing::query()->updateOrCreate(
                ['infoid' => $infoid],
                $this->mapCarInfoToListing($row, $infoid),
            );

            $this->syncPhotos($listing, $row['catepiclist'] ?? []);

            if (! empty($row['paramkey'])) {
                $this->syncReport($infoid, (string) $row['paramkey']);
            }

            return $listing->fresh(['photos', 'report']);
        });
    }

    public function checkpoint(string $name): ScrapeCheckpoint
    {
        return ScrapeCheckpoint::query()->firstOrCreate(
            ['name' => $name],
            ['last_page' => 0],
        );
    }

    private function syncSpecAttributes(int $specId): void
    {
        $spec = Spec::query()->find($specId);
        if (! $spec || ($spec->raw_param && $spec->raw_config)) {
            return;
        }

        try {
            $param = $this->api->specParam($specId);
            $config = $this->api->specConfig($specId);
            $spec->update([
                'raw_param' => $param['result'] ?? null,
                'raw_config' => $config['result'] ?? null,
            ]);
        } catch (\Throwable) {
            // spec endpoints may rate-limit; detail row is still saved
        }
    }

    private function syncReport(int $infoid, string $paramkey): void
    {
        try {
            $payload = $this->api->reportSummary($paramkey);
            $result = $payload['result'] ?? [];

            ListingReport::query()->updateOrCreate(
                ['infoid' => $infoid],
                [
                    'claims_count' => isset($result['claimsCount']) ? (int) $result['claimsCount'] : null,
                    'latest_mileage_km' => isset($result['latestMileage']) ? (int) $result['latestMileage'] : null,
                    'insurance_url' => $result['insuranceUrl'] ?? null,
                    'maintenance_url' => $result['maintenanceUrl'] ?? null,
                    'raw_summary' => $result,
                ],
            );
        } catch (\Throwable) {
        }
    }

    /**
     * @param  list<array{list?: list<string>, title?: string}>|mixed  $catepiclist
     */
    private function syncPhotos(Listing $listing, mixed $catepiclist): void
    {
        $urls = [];
        if (is_array($catepiclist)) {
            foreach ($catepiclist as $group) {
                foreach ($group['list'] ?? [] as $url) {
                    if (is_string($url) && $url !== '') {
                        $urls[] = $url;
                    }
                }
            }
        }

        ListingPhoto::query()->where('infoid', $listing->infoid)->delete();

        foreach (array_values(array_unique($urls)) as $index => $url) {
            ListingPhoto::query()->create([
                'infoid' => $listing->infoid,
                'sort_order' => $index,
                'preview_url' => ImageUrlHelper::preview($url),
                'large_url' => ImageUrlHelper::large($url),
                'original_url' => ImageUrlHelper::original($url),
                'hash' => ImageUrlHelper::hashFromUrl($url),
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function upsertFromSearchRow(array $row): void
    {
        $infoid = (int) ($row['infoid'] ?? 0);
        if ($infoid <= 0) {
            return;
        }

        $payload = [
            'title' => trim((string) ($row['carname'] ?? '')),
            'price_usd' => $this->numeric($row['price'] ?? null),
            'mileage_km' => isset($row['mileage']) ? (int) $row['mileage'] : null,
            'reg_date' => $row['regdate'] ?? null,
            'fuel_name' => $row['fuelname'] ?? null,
            'dealer_id' => isset($row['dealerid']) ? (int) $row['dealerid'] : null,
            'city_slug' => $row['cname'] ?? null,
            'cert_status' => isset($row['cert_status']) ? (int) $row['cert_status'] : null,
            'claim_status' => isset($row['claim_status']) ? (int) $row['claim_status'] : null,
            'mr_status' => isset($row['mr_status']) ? (int) $row['mr_status'] : null,
            'whatsapp_url' => $row['whatsapp_url'] ?? null,
            'wechat_url' => $row['wechat_url'] ?? null,
            'detail_url' => 'https://global.che168.com/en/detail/'.$infoid,
            'language' => config('che168.language'),
            'status' => 'active',
            'scraped_at' => now(),
        ];

        if ($brandId = $this->resolveBrandId($row)) {
            $payload['brand_id'] = $brandId;
        }
        if ($seriesId = $this->resolveSeriesId($row, $payload['brand_id'] ?? null)) {
            $payload['series_id'] = $seriesId;
        }

        Listing::query()->updateOrCreate(['infoid' => $infoid], $payload);

        if (! empty($row['imageurl'])) {
            $url = (string) $row['imageurl'];
            ListingPhoto::query()->updateOrCreate(
                [
                    'infoid' => $infoid,
                    'sort_order' => 0,
                ],
                [
                    'preview_url' => ImageUrlHelper::preview($url),
                    'large_url' => ImageUrlHelper::large($url),
                    'original_url' => ImageUrlHelper::original($url),
                    'hash' => ImageUrlHelper::hashFromUrl($url),
                ],
            );
        }
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private function mapCarInfoToListing(array $row, int $infoid): array
    {
        return [
            'spec_id' => isset($row['specid']) ? (int) $row['specid'] : null,
            'brand_id' => isset($row['brandid']) ? (int) $row['brandid'] : null,
            'series_id' => isset($row['seriesid']) ? (int) $row['seriesid'] : null,
            'dealer_id' => isset($row['dealeid'])
                ? (int) $row['dealeid']
                : (isset($row['dealerid']) ? (int) $row['dealerid'] : null),
            'title' => trim((string) ($row['carname'] ?? '')),
            'price_usd' => $this->numeric($row['price'] ?? null),
            'export_fee_usd' => $this->numeric($row['export_fee'] ?? null),
            'port_fee_usd' => $this->numeric($row['port_fee'] ?? null),
            'mileage_km' => isset($row['mileage']) ? (int) $row['mileage'] : null,
            'reg_date' => $row['regdate'] ?? null,
            'produce_date' => ! empty($row['producedate']) ? Carbon::parse($row['producedate']) : null,
            'city_slug' => $row['cname'] ?? null,
            'city_id' => isset($row['cid']) ? (int) $row['cid'] : null,
            'color' => $row['color'] ?? null,
            'vin_masked' => $row['vincode'] ?? null,
            'fuel_name' => $row['fuelname'] ?? null,
            'engine' => $row['engine'] ?? null,
            'gearbox' => $row['gearbox'] ?? null,
            'driving_mode' => $row['drivingmode'] ?? null,
            'steering' => $row['steering'] ?? null,
            'body_type' => $row['structure'] ?? null,
            'doors' => isset($row['structuredoor']) ? (int) $row['structuredoor'] : null,
            'seats' => isset($row['setcount']) ? (int) $row['setcount'] : null,
            'class_level' => $row['level'] ?? null,
            'dimension' => $row['dimension'] ?? null,
            'curb_weight_kg' => isset($row['curbweight']) ? (int) $row['curbweight'] : null,
            'certification' => isset($row['certification_status']) ? (int) $row['certification_status'] : null,
            'paramkey' => $row['paramkey'] ?? null,
            'report_url' => $row['report_url'] ?? null,
            'detail_url' => 'https://global.che168.com/en/detail/'.$infoid,
            'whatsapp_url' => $row['whatsapp_url'] ?? null,
            'wechat_url' => $row['wechat_url'] ?? null,
            'language' => config('che168.language'),
            'status' => 'active',
            'scraped_at' => now(),
            'raw_carinfo' => $row,
        ];
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function resolveBrandId(array $row): ?int
    {
        $id = (int) ($row['brandid'] ?? $row['bid'] ?? 0);
        $name = trim((string) ($row['brandname'] ?? ''));

        if ($id > 0) {
            Brand::query()->updateOrCreate(
                ['brand_id' => $id],
                ['name' => $name !== '' ? $name : 'Brand '.$id],
            );

            return $id;
        }

        if ($name === '') {
            return null;
        }

        $found = Brand::query()->where('name', $name)->value('brand_id');

        return $found ? (int) $found : null;
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function resolveSeriesId(array $row, ?int $brandId): ?int
    {
        $id = (int) ($row['seriesid'] ?? 0);
        $name = trim((string) ($row['seriesname'] ?? ''));

        if ($id > 0 && $brandId) {
            Series::query()->updateOrCreate(
                ['series_id' => $id],
                ['brand_id' => $brandId, 'name' => $name !== '' ? $name : 'Series '.$id],
            );

            return $id;
        }

        if ($name === '' || ! $brandId) {
            return null;
        }

        $found = Series::query()->where('brand_id', $brandId)->where('name', $name)->value('series_id');

        return $found ? (int) $found : null;
    }

    private function numeric(mixed $value): ?float
    {
        if ($value === null || $value === '' || $value === '--') {
            return null;
        }

        return (float) $value;
    }
}