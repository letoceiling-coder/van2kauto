<?php

namespace App\Services\Showcase;

use App\Models\Listing;
use App\Support\Settings;

/**
 * Цена «от» в рублях по формуле из «Настроек»:
 *   (цена у продавца + экспортный сбор + портовый сбор) × курс $ × коэффициент «под ключ» + фикс. добавка, округление вниз.
 * Это ориентир для витрины: точную смету менеджер считает по конкретному лоту.
 */
class PriceEstimator
{
    public static function fromUsd(?float $carUsd, float $exportUsd = 0, float $portUsd = 0): ?int
    {
        if (! $carUsd || $carUsd <= 0) {
            return null;
        }
        $rate = (float) str_replace(',', '.', (string) Settings::get('price_usd_rate', '95'));
        $mult = (float) str_replace(',', '.', (string) Settings::get('price_multiplier', '1.35'));
        $fixed = (int) Settings::get('price_fixed_rub', '250000');
        $round = max(1000, (int) Settings::get('price_round_rub', '10000'));

        $rub = ($carUsd + $exportUsd + $portUsd) * $rate * $mult + $fixed;

        return (int) (floor($rub / $round) * $round);
    }

    public static function fromListing(Listing $listing): ?int
    {
        return self::fromUsd(
            $listing->price_usd !== null ? (float) $listing->price_usd : null,
            (float) ($listing->export_fee_usd ?? 0),
            (float) ($listing->port_fee_usd ?? 0),
        );
    }

    /** Пример расчёта для подсказки в настройках. */
    public static function example(float $carUsd = 12000): array
    {
        return ['usd' => $carUsd, 'rub' => self::fromUsd($carUsd, 400, 500)];
    }
}
