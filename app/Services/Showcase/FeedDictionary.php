<?php

namespace App\Services\Showcase;

/** Перевод значений che168 в справочники витрины. */
class FeedDictionary
{
    public static function engine(?string $fuel): string
    {
        $f = mb_strtolower((string) $fuel);

        return match (true) {
            str_contains($f, 'range') || str_contains($f, 'extend') => 'erev',
            str_contains($f, 'plug') => 'phev',
            str_contains($f, 'hybrid') => 'hybrid',
            str_contains($f, 'electric') => 'ev',
            str_contains($f, 'diesel') => 'diesel',
            default => 'petrol',
        };
    }

    public static function drive(?string $mode): string
    {
        $m = mb_strtolower((string) $mode);

        return match (true) {
            str_contains($m, 'four') || str_contains($m, '4wd') || str_contains($m, 'awd') || str_contains($m, 'all-wheel') => 'AWD',
            str_contains($m, 'rear-wheel') || str_contains($m, 'rear wheel') => 'RWD',
            default => 'FWD',
        };
    }

    public static function body(?string $body, ?string $class = null): string
    {
        $b = mb_strtolower(trim((string) $body.' '.$class));

        return match (true) {
            str_contains($b, 'mpv') || str_contains($b, 'minivan') => 'minivan',
            str_contains($b, 'pickup') => 'pickup',
            str_contains($b, 'wagon') || str_contains($b, 'estate') => 'wagon',
            str_contains($b, 'coupe') || str_contains($b, 'convertible') || str_contains($b, 'sports') => 'coupe',
            str_contains($b, 'hatch') => 'hatchback',
            str_contains($b, 'sedan') => 'sedan',
            str_contains($b, 'large suv') || str_contains($b, 'full-size suv') || str_contains($b, 'off-road') => 'suv',
            str_contains($b, 'suv') || str_contains($b, 'crossover') => 'crossover',
            default => 'sedan',
        };
    }

    public static function gearbox(?string $gearbox): ?string
    {
        $g = mb_strtolower((string) $gearbox);
        if ($g === '' || $g === '--') {
            return null;
        }

        return match (true) {
            str_contains($g, 'single-speed') || str_contains($g, 'electric vehicle') => 'Редуктор',
            str_contains($g, 'dual-clutch') || str_contains($g, 'dct') || str_contains($g, 'dsg') => 'Робот (DCT)',
            str_contains($g, 'cvt') || str_contains($g, 'continuously') => 'Вариатор',
            str_contains($g, 'manual') => 'Механика',
            str_contains($g, 'auto') => 'Автомат',
            default => $gearbox,
        };
    }

    public static function steering(?string $steering): string
    {
        return str_contains(mb_strtolower((string) $steering), 'right') ? 'right' : 'left';
    }
}
