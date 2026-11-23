<?php

namespace App\Services\Automarket;

use App\Models\AuctionLot;
use App\Models\AuctionMaker;
use App\Models\AuctionModel;
use App\Models\AuctionPhoto;
use Illuminate\Support\Carbon;

class CatalogSync
{
    public function __construct(
        private readonly SessionClient $client,
        private readonly HtmlParser $parser,
    ) {}

    public function proxyLabel(): string
    {
        return $this->client->proxyLabel();
    }

    public function syncMakers(): int
    {
        $html = $this->client->get('/auctions/?p=project/searchform&searchtype=max&s&ld');
        $count = 0;
        foreach ($this->parser->makers($html) as $maker) {
            AuctionMaker::query()->updateOrCreate(
                ['maker_id' => $maker['maker_id']],
                ['name' => $maker['name'], 'group_name' => $maker['group_name']],
            );
            $count++;
        }

        return $count;
    }

    public function syncModels(int $makerId): int
    {
        $body = $this->client->get('/auctions/?p=project/searchform&searchtype=max&rs=loadModels&rst=&rsrnd='
            .now()->getTimestampMs().'&rsargs[]='.$makerId.'&rsargs[]=');
        $count = 0;
        foreach ($this->parser->models($body) as $model) {
            AuctionModel::query()->updateOrCreate(
                ['model_id' => $model['model_id']],
                ['maker_id' => $makerId, 'name' => $model['name']],
            );
            $count++;
        }

        return $count;
    }

    /**
     * @param  array<string, scalar|null>  $filters
     * @return array{lots: list<array<string, mixed>>, total: int}
     */
    public function searchPage(int $page, array $filters = []): array
    {
        $html = $this->client->get('/auctions', array_merge([
            'p' => 'project/findlots',
            's' => 'lot_date',
            'o' => 'd',
            'ld' => '',
            'rollback_data' => '',
            'lot' => '',
            'mrk' => '',
            'mdl' => '-1',
            'year1' => '',
            'year2' => '',
            'transmission' => '',
            'mileage1' => '',
            'mileage2' => '',
            'awd' => '',
            'v1' => '',
            'v2' => '',
            'type' => '',
            'start1' => '',
            'start2' => '',
            'word' => '',
            'manage_code' => '0',
            'search_title' => '',
            'pg' => $page,
            'vs' => (int) config('automarket.page_size', 100),
        ], $filters));
        if (! $this->parser->isResultsPage($html)) {
            throw new \RuntimeException('Страница поиска не содержит список лотов.');
        }

        return [
            'lots' => $this->parser->lots($html),
            'total' => $this->parser->totalLots($html),
        ];
    }

    /** @param  array<string, mixed>  $row */
    public function saveListRow(array $row): AuctionLot
    {
        $makerId = AuctionMaker::query()->where('name', $row['maker_name'])->value('maker_id');
        $modelId = null;
        if ($makerId && ! empty($row['model_name'])) {
            $modelId = AuctionModel::query()
                ->where('maker_id', $makerId)
                ->whereRaw('lower(name) = ?', [mb_strtolower((string) $row['model_name'])])
                ->value('model_id');
        }

        $lot = AuctionLot::query()->updateOrCreate(
            ['lot_id' => $row['lot_id']],
            [
                'bid' => $row['bid'] ?: null,
                'auction_name' => $row['auction_name'] ?: null,
                'auction_code' => $row['auction_code'],
                'auction_at' => $this->date($row['auction_at'] ?? null),
                'maker_id' => $makerId ? (int) $makerId : null,
                'model_id' => $modelId ? (int) $modelId : null,
                'maker_name' => $row['maker_name'] ?: null,
                'model_name' => $row['model_name'] ?: null,
                'grade' => $row['grade'] ?: null,
                'year' => $row['year'],
                'mileage_km' => $row['mileage_km'],
                'displacement_cc' => $row['displacement_cc'],
                'transmission' => $row['transmission'] ?: null,
                'color' => $row['color'] ?: null,
                'model_type' => $row['model_type'] ?: null,
                'equipment' => $row['equipment'] ?: null,
                'start_price_jpy' => $row['start_price_jpy'],
                'end_price_jpy' => $row['end_price_jpy'],
                'result' => $row['result'] ?: null,
                'score' => $row['score'] ?: null,
                'status' => 'active',
                'scraped_at' => now(),
            ],
        );

        if (! empty($row['photo'])) {
            AuctionPhoto::query()->updateOrCreate(
                ['lot_id' => $lot->lot_id, 'sort_order' => 0],
                ['preview_url' => $row['photo'], 'large_url' => preg_replace('/\?w=\d+/', '', (string) $row['photo'])],
            );
        }

        return $lot;
    }

    public function saveDetail(int $lotId): void
    {
        $html = $this->client->get('/auctions/', [
            'p' => 'project/lot',
            'id' => $lotId,
            's' => '',
        ]);
        $detail = $this->parser->detail($html);
        $attributes = $detail['attributes'];
        $lot = AuctionLot::query()->find($lotId);
        if (! $lot) {
            return;
        }

        $start = $this->yen($attributes['start price'] ?? null);
        $end = $this->yen($attributes['final price'] ?? null);
        $lot->fill([
            'fuel' => $attributes['fuel'] ?? $lot->fuel,
            'chassis' => $attributes['chassis number'] ?? $lot->chassis,
            'start_price_jpy' => $start ?? $lot->start_price_jpy,
            'end_price_jpy' => $end ?? $lot->end_price_jpy,
            'attributes' => $attributes,
            'scraped_at' => now(),
        ])->save();

        if ($detail['photos'] === []) {
            return;
        }

        AuctionPhoto::query()->where('lot_id', $lotId)->delete();
        foreach ($detail['photos'] as $index => $url) {
            $preview = str_contains($url, 'h=') ? $url : $url.(str_contains($url, '?') ? '&' : '?').'h=96';
            AuctionPhoto::query()->create([
                'lot_id' => $lotId,
                'sort_order' => $index,
                'preview_url' => $preview,
                'large_url' => preg_replace('/&h=\d+/', '', $url),
            ]);
        }
    }

    private function yen(?string $value): ?int
    {
        if ($value === null || ! preg_match('/\d/', $value)) {
            return null;
        }
        if (preg_match('/([\d.]+)\s*ten thousand/i', $value, $match)) {
            return (int) round((float) $match[1] * 10000);
        }
        $digits = preg_replace('/\D/', '', $value) ?? '';

        return $digits === '' ? null : (int) $digits;
    }

    private function date(mixed $value): ?Carbon
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }
}
