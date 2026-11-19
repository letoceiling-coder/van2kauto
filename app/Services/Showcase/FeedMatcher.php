<?php

namespace App\Services\Showcase;

use App\Models\Listing;
use App\Models\Series;
use App\Models\ShowcaseCar;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Совпадения карточки витрины с объявлениями фида che168:
 * та же серия (или марка+модель по названию), год ±1, при наличии — та же комплектация и мощность ±10%.
 */
class FeedMatcher
{
    /** @return array{query: string, total: int, exact_spec: int, price_min: float|null, price_median: float|null, items: Collection} */
    public function match(ShowcaseCar $car, int $limit = 12): array
    {
        $query = $this->query($car);
        if ($query === null) {
            return ['query' => 'нет серии', 'total' => 0, 'exact_spec' => 0, 'price_min' => null, 'price_median' => null, 'items' => collect()];
        }

        $total = (clone $query)->count();
        $exact = $car->spec_id ? (clone $query)->where('spec_id', $car->spec_id)->count() : 0;
        $prices = (clone $query)->whereNotNull('price_usd')->orderBy('price_usd')->pluck('price_usd')->map(fn ($p) => (float) $p);

        $items = (clone $query)
            ->with(['photos' => fn ($q) => $q->where('sort_order', 0), 'spec'])
            ->when($car->spec_id, fn ($q) => $q->orderByRaw('case when spec_id = ? then 0 else 1 end', [$car->spec_id]))
            ->orderBy('price_usd')
            ->limit($limit)
            ->get();

        return [
            'query' => $this->describe($car),
            'total' => $total,
            'exact_spec' => $exact,
            'price_min' => $prices->first(),
            'price_median' => $prices->isEmpty() ? null : $prices->values()[intdiv($prices->count(), 2)],
            'items' => $items,
        ];
    }

    public function count(ShowcaseCar $car): int
    {
        return $this->query($car)?->count() ?? 0;
    }

    private function query(ShowcaseCar $car): ?Builder
    {
        $seriesId = $car->series_id ?: $this->guessSeries($car);
        if (! $seriesId) {
            return null;
        }

        return Listing::query()
            ->where('status', 'active')
            ->where('series_id', $seriesId)
            ->when($car->year, fn ($q) => $q->where('reg_date', '>=', (string) ($car->year - 1))->where('reg_date', '<', (string) ($car->year + 2)))
            ->when($car->power_hp, fn ($q) => $q->where(fn ($w) => $w->whereNull('power_hp')->orWhereBetween('power_hp', [(int) floor($car->power_hp * 0.9), (int) ceil($car->power_hp * 1.1)])));
    }

    private function guessSeries(ShowcaseCar $car): ?int
    {
        $op = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';

        return Series::query()
            ->where('name', $op, $car->model)
            ->whereHas('brand', fn ($b) => $b->where('name', $op, $car->brand))
            ->value('series_id');
    }

    private function describe(ShowcaseCar $car): string
    {
        return trim($car->brand.' '.$car->model.' · '.($car->year - 1).'–'.($car->year + 1).($car->power_hp ? ' · '.$car->power_hp.' л.с. ±10%' : ''));
    }
}
