<?php

namespace App\Services\Showcase;

use App\Models\Listing;
use App\Models\ShowcaseCar;

/** Объявление фида → черновик карточки витрины (или обновление полей существующей). */
class ListingImporter
{
    public function __construct(private readonly PhotoStore $photos) {}

    /** @return array<string, mixed> */
    public function attributes(Listing $listing): array
    {
        $listing->loadMissing(['brand', 'series', 'spec']);
        $year = $listing->reg_date ? (int) substr((string) $listing->reg_date, 0, 4) : (int) now()->year;

        return [
            'source_infoid' => (int) $listing->infoid,
            'brand_id' => $listing->brand_id,
            'series_id' => $listing->series_id,
            'spec_id' => $listing->spec_id,
            'country' => 'china',
            'brand' => $listing->brand?->name ?? strtok((string) $listing->title, ' '),
            'model' => $listing->series?->name ?? (string) $listing->title,
            'trim' => $listing->spec?->name,
            'year' => $year,
            'condition' => (int) $listing->mileage_km < 1000 ? 'new' : 'used',
            'body' => FeedDictionary::body($listing->body_type, $listing->class_level),
            'engine' => FeedDictionary::engine($listing->fuel_name),
            'displacement_l' => $listing->displacement_l,
            'power_hp' => $listing->power_hp,
            'drive' => FeedDictionary::drive($listing->driving_mode),
            'gearbox' => FeedDictionary::gearbox($listing->gearbox),
            'steering' => FeedDictionary::steering($listing->steering),
            'mileage_km' => (int) $listing->mileage_km,
        ];
    }

    public function createDraft(Listing $listing, int $photoLimit = 8): ShowcaseCar
    {
        $car = ShowcaseCar::query()->create($this->attributes($listing) + [
            'is_published' => false,
            'status' => 'order',
            'sort_order' => (int) ShowcaseCar::query()->max('sort_order') + 10,
        ]);
        $this->importPhotos($car, $listing, $photoLimit);

        return $car;
    }

    /** Подставить характеристики из объявления в существующую карточку (цена, тексты и фото не трогаются). */
    public function fill(ShowcaseCar $car, Listing $listing): ShowcaseCar
    {
        $car->update($this->attributes($listing));

        return $car;
    }

    /** @param  list<int>|null  $sorts  какие фото взять (sort_order), null — первые $limit */
    public function importPhotos(ShowcaseCar $car, Listing $listing, int $limit = 8, ?array $sorts = null): int
    {
        $photos = $listing->photos()->orderBy('sort_order')
            ->when($sorts !== null, fn ($q) => $q->whereIn('sort_order', $sorts))
            ->limit($sorts !== null ? 50 : $limit)
            ->get();

        $n = 0;
        foreach ($photos as $photo) {
            $url = $photo->large_url ?: $photo->original_url ?: $photo->preview_url;
            if ($url && $this->photos->fromUrl($car, (string) $url)) {
                $n++;
            }
        }

        return $n;
    }
}
