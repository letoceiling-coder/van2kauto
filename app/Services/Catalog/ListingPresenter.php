<?php

namespace App\Services\Catalog;

use App\Models\Listing;
use App\Models\ListingPhoto;

class ListingPresenter
{
    /** @return array<string, mixed> */
    public function card(Listing $listing): array
    {
        $photo = $listing->photos->first();

        return [
            'infoid' => (int) $listing->infoid,
            'title' => $listing->title,
            'price_usd' => $listing->price_usd !== null ? (float) $listing->price_usd : null,
            'export_fee_usd' => $listing->export_fee_usd !== null ? (float) $listing->export_fee_usd : null,
            'port_fee_usd' => $listing->port_fee_usd !== null ? (float) $listing->port_fee_usd : null,
            'mileage_km' => $listing->mileage_km,
            'year' => $listing->reg_date ? (int) substr((string) $listing->reg_date, 0, 4) : null,
            'reg_date' => $listing->reg_date,
            'fuel' => $listing->fuel_name,
            'gearbox' => $listing->gearbox,
            'body_type' => $listing->body_type,
            'color' => $listing->color,
            'engine' => $listing->engine,
            'driving_mode' => $listing->driving_mode,
            'steering' => $listing->steering,
            'seats' => $listing->seats,
            'doors' => $listing->doors,
            'class_level' => $listing->class_level,
            'city' => $listing->city_slug,
            'status' => $listing->status,
            'category' => $listing->brand ? [
                'id' => $listing->brand->brand_id,
                'name' => $listing->brand->name,
            ] : null,
            'model' => $listing->series ? [
                'id' => $listing->series->series_id,
                'name' => $listing->series->name,
                'category_id' => $listing->series->brand_id,
            ] : null,
            'spec' => $listing->spec ? [
                'id' => $listing->spec->spec_id,
                'name' => $listing->spec->name,
                'year_label' => $listing->spec->year_label,
            ] : null,
            'photo' => $photo ? $this->photo($photo) : null,
            'photos_count' => $listing->photos_count ?? $listing->photos->count(),
            'scraped_at' => $listing->scraped_at?->toIso8601String(),
        ];
    }

    /** @return array<string, mixed> */
    public function detail(Listing $listing, bool $withRaw = false): array
    {
        $data = $this->card($listing);
        $data['photos'] = $listing->photos->map(fn (ListingPhoto $photo) => $this->photo($photo))->values()->all();
        $data['properties'] = [
            'vin' => $listing->vin_masked,
            'dimension' => $listing->dimension,
            'curb_weight_kg' => $listing->curb_weight_kg,
            'produce_date' => $listing->produce_date?->toDateString(),
            'city_id' => $listing->city_id,
            'dealer_id' => $listing->dealer_id,
            'cert_status' => $listing->cert_status,
            'claim_status' => $listing->claim_status,
            'mr_status' => $listing->mr_status,
            'certification' => $listing->certification,
            'detail_url' => $listing->detail_url,
            'report_url' => $listing->report_url,
            'whatsapp_url' => $listing->whatsapp_url,
            'wechat_url' => $listing->wechat_url,
        ];
        $data['report'] = $listing->report ? [
            'claims_count' => $listing->report->claims_count,
            'latest_mileage_km' => $listing->report->latest_mileage_km,
            'insurance_url' => $listing->report->insurance_url,
            'maintenance_url' => $listing->report->maintenance_url,
        ] : null;
        $data['spec_groups'] = $this->groups(data_get($listing->spec, 'raw_param.paramtypeitems', []), 'paramitems');
        $data['equipment_groups'] = $this->groups(data_get($listing->spec, 'raw_config.configtypeitems', []), 'configitems');
        if ($withRaw) {
            $data['raw'] = $listing->raw_carinfo;
        }

        return $data;
    }

    /** @return array<string, string|int|null> */
    private function photo(ListingPhoto $photo): array
    {
        return [
            'sort' => $photo->sort_order,
            'preview' => $photo->preview_url,
            'large' => $photo->large_url,
            'original' => $photo->original_url,
        ];
    }

    /**
     * @param  mixed  $groups
     * @return list<array{name: string, items: list<array{name: string, value: string}>}>
     */
    private function groups(mixed $groups, string $itemsKey): array
    {
        if (! is_array($groups)) {
            return [];
        }

        $out = [];
        foreach ($groups as $group) {
            if (! is_array($group)) {
                continue;
            }
            $items = [];
            foreach ($group[$itemsKey] ?? [] as $item) {
                if (! is_array($item)) {
                    continue;
                }
                $items[] = [
                    'name' => (string) ($item['name'] ?? ''),
                    'value' => $this->itemValue($item),
                ];
            }
            $out[] = [
                'name' => (string) ($group['name'] ?? $group['title'] ?? ''),
                'items' => $items,
            ];
        }

        return $out;
    }

    /** @param  array<string, mixed>  $item */
    private function itemValue(array $item): string
    {
        if (isset($item['value']) && is_scalar($item['value'])) {
            return (string) $item['value'];
        }

        $vals = collect($item['valueitems'] ?? [])->map(function ($value) {
            if (is_scalar($value)) {
                return (string) $value;
            }
            if (is_array($value)) {
                return (string) ($value['value'] ?? $value['name'] ?? '');
            }

            return '';
        })->filter()->implode(', ');

        return $vals;
    }
}
