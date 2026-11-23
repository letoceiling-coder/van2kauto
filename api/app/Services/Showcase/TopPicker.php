<?php

namespace App\Services\Showcase;

use App\Models\Listing;
use App\Models\Series;
use App\Models\ShowcaseCar;
use App\Support\Settings;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Топ моделей из фида che168 для витрины — по реальным данным:
 *  - только «проходные»: мощность ≤ лимита, возраст в коридоре из настроек;
 *  - только ДВС, дизель, обычные и мягкие гибриды (у PHEV/EREV/электро утильсбор считается по суммарной мощности);
 *  - модели ранжируются по числу активных предложений на рынке;
 *  - для каждой модели берём самое доступное объявление с хорошими фото и пробегом до 120 тыс. км.
 */
class TopPicker
{
    public function __construct(private readonly ListingImporter $importer) {}

    public function eligible(): Builder
    {
        $year = (int) now()->year;
        $minAge = Settings::int('utilization_min_age', 3);
        $maxAge = Settings::int('utilization_max_age', 5);

        return Listing::query()
            ->where('status', 'active')
            ->whereNotNull('series_id')
            ->whereNotNull('price_usd')
            ->where('power_hp', '<=', Settings::int('utilization_max_hp', 160))
            ->where('reg_date', '>=', (string) ($year - $maxAge))
            ->where('reg_date', '<', (string) ($year - $minAge + 1))
            ->where(fn ($q) => $q->whereIn('fuel_name', ['Gasoline', 'Diesel', 'Hybrid'])->orWhere('fuel_name', 'like', 'Gasoline +%'));
    }

    /** @return Collection<int, array{series: Series, count: int, listing: Listing}> */
    public function pick(int $limit = 10): Collection
    {
        $inShowcase = ShowcaseCar::query()->whereNotNull('series_id')->pluck('series_id')->all();

        $ranked = $this->eligible()
            ->when($inShowcase, fn ($q) => $q->whereNotIn('series_id', $inShowcase))
            ->select('series_id', DB::raw('count(*) as offers'))
            ->groupBy('series_id')
            ->orderByDesc('offers')
            ->limit($limit * 3)
            ->get();

        $out = collect();
        foreach ($ranked as $row) {
            $listing = $this->eligible()
                ->where('series_id', $row->series_id)
                ->where(fn ($q) => $q->whereNull('mileage_km')->orWhere('mileage_km', '<=', 120000))
                ->whereHas('photos', null, '>=', 6)
                ->orderBy('price_usd')
                ->first();
            $series = Series::query()->with('brand')->find($row->series_id);
            if ($listing && $series) {
                $out->push(['series' => $series, 'count' => (int) $row->offers, 'listing' => $listing]);
            }
            if ($out->count() >= $limit) {
                break;
            }
        }

        return $out;
    }

    public static function plural(int $n, string $one, string $few, string $many): string
    {
        $m10 = $n % 10;
        $m100 = $n % 100;
        $word = ($m10 === 1 && $m100 !== 11) ? $one : (($m10 >= 2 && $m10 <= 4 && ($m100 < 12 || $m100 > 14)) ? $few : $many);

        return "{$n} {$word}";
    }

    /** Создать карточки витрины. Возвращает созданные. */
    public function build(int $limit = 10, bool $publish = false, int $photos = 8): Collection
    {
        $startSort = (int) ShowcaseCar::query()->max('sort_order');
        $made = collect();

        foreach ($this->pick($limit)->values() as $i => $item) {
            /** @var Listing $listing */
            $listing = $item['listing'];
            $car = $this->importer->createDraft($listing, $photos);
            $price = PriceEstimator::fromListing($listing);
            $car->update([
                'price_from_rub' => $price,
                'is_hit' => true,
                'is_published' => $publish && $price !== null && $car->photos()->exists(),
                'sort_order' => $startSort + ($i + 1) * 10,
                'summary' => 'Одна из самых востребованных моделей: сейчас '.self::plural($item['count'], 'предложение', 'предложения', 'предложений').' на рынке Китая. Подберём лучший вариант под ваш бюджет и проверим до выкупа.',
                'highlights' => array_values(array_filter([
                    $listing->power_hp ? "{$listing->power_hp} л.с. — льготный утильсбор" : null,
                    'Отчёт об осмотре и истории до оплаты',
                    'Доставка и растаможка под ключ до Анапы',
                ])),
                'badge' => 'Топ-'.($i + 1),
            ]);
            $made->push($car->fresh());
        }

        return $made;
    }
}
