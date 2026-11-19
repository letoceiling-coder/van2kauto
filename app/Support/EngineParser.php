<?php

namespace App\Support;

/**
 * Разбор строки двигателя che168: "1.5T 181HP L4", "2.0L 171 horsepower L4", "Range Extender 156 HP".
 * Для электро строка "--" — мощность берётся из характеристик комплектации (spec raw_param).
 */
class EngineParser
{
    public static function powerHp(?string $engine): ?int
    {
        if (! $engine) {
            return null;
        }
        if (preg_match('/(\d{2,4})\s*-?\s*(?:hp|horsepower|ps|л\.?\s*с)\b/iu', $engine, $m)) {
            $hp = (int) $m[1];

            return $hp >= 30 && $hp <= 2500 ? $hp : null;
        }

        return null;
    }

    public static function displacementL(?string $engine): ?float
    {
        if ($engine && preg_match('/\b(\d\.\d)\s*[TL]\b/i', $engine, $m)) {
            return (float) $m[1];
        }

        return null;
    }

    /** Мощность из raw_param комплектации: двигатель, иначе суммарная мощность электромоторов. */
    public static function powerFromSpecParam(mixed $rawParam): ?int
    {
        $groups = data_get($rawParam, 'paramtypeitems', []);
        if (! is_array($groups)) {
            return null;
        }
        $engineHp = null;
        $motorHp = null;
        $motorKw = null;
        foreach ($groups as $group) {
            foreach ((array) ($group['paramitems'] ?? []) as $item) {
                $name = mb_strtolower((string) ($item['name'] ?? ''));
                $value = self::value($item);
                if (! is_numeric($value)) {
                    continue;
                }
                if ($engineHp === null && str_contains($name, 'maximum horsepower')) {
                    $engineHp = (int) $value;
                } elseif ($motorHp === null && str_contains($name, 'motor horsepower')) {
                    $motorHp = (int) $value;
                } elseif ($motorKw === null && str_contains($name, 'total motor power (kw)')) {
                    $motorKw = (float) $value;
                }
            }
        }

        return $engineHp ?? $motorHp ?? ($motorKw ? (int) round($motorKw * 1.35962) : null);
    }

    private static function value(array $item): ?string
    {
        if (isset($item['value']) && is_scalar($item['value'])) {
            return trim((string) $item['value']);
        }
        $first = $item['valueitems'][0] ?? null;
        if (is_array($first)) {
            return isset($first['value']) ? trim((string) $first['value']) : null;
        }

        return is_scalar($first) ? trim((string) $first) : null;
    }
}
