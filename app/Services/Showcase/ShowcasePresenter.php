<?php

namespace App\Services\Showcase;

use App\Models\ShowcaseCar;
use App\Models\ShowcasePhoto;

/** JSON карточки для сайта. Данные донора (infoid, ссылки che168, цена в USD) наружу не отдаются. */
class ShowcasePresenter
{
    /** @return array<string, mixed> */
    public function car(ShowcaseCar $car): array
    {
        $title = trim($car->title().' '.$car->year);

        return [
            'slug' => $car->slug,
            'brand' => $car->brand,
            'model' => $car->model,
            'generation' => $car->generation,
            'trim' => $car->trim,
            'year' => (int) $car->year,
            'country' => $car->country,
            'condition' => $car->condition,
            'body' => $car->body,
            'engine' => $car->engine,
            'engine_label' => $car->engine_label ?: $this->engineLabel($car),
            'displacement_l' => $car->displacement_l !== null ? (float) $car->displacement_l : null,
            'power_hp' => $car->power_hp,
            'drive' => $car->drive,
            'gearbox' => $car->gearbox,
            'steering' => $car->steering,
            'mileage_km' => (int) $car->mileage_km,
            'price_from_rub' => $car->price_from_rub !== null ? (int) $car->price_from_rub : null,
            'status' => $car->status,
            'utilization_ok' => $car->utilizationOk(),
            'hit' => (bool) $car->is_hit,
            'badge' => $car->badge,
            'summary' => $car->summary,
            'highlights' => array_values(array_filter((array) $car->highlights)),
            'eta' => [(int) $car->eta_from, (int) $car->eta_to],
            'images' => $car->photos->map(fn (ShowcasePhoto $p, int $i) => [
                'src' => $p->url(),
                'alt' => $title.($i === 0 ? '' : ', фото '.($i + 1)),
                'width' => $p->width,
                'height' => $p->height,
            ])->values()->all(),
            'sort' => (int) $car->sort_order,
            'updated_at' => $car->updated_at?->toIso8601String(),
        ];
    }

    private function engineLabel(ShowcaseCar $car): string
    {
        $parts = [ShowcaseCar::ENGINES[$car->engine] ?? $car->engine];
        if ($car->displacement_l) {
            $parts[] = number_format((float) $car->displacement_l, 1, '.', '').' л';
        }
        if ($car->power_hp) {
            $parts[] = $car->power_hp.' л.с.';
        }

        return implode(', ', $parts);
    }
}
