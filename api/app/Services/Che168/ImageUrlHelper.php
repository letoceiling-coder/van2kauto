<?php

namespace App\Services\Che168;

class ImageUrlHelper
{
    public static function preview(string $url): string
    {
        $base = self::stripQuery($url);

        if (! str_contains($base, '640x480')) {
            $base = preg_replace('/\d+x\d+_c42_autohomecar__/', '640x480_c42_autohomecar__', $base) ?? $base;
        }

        if (! str_ends_with($base, '.webp') && str_contains($base, '.jpg')) {
            $base = preg_replace('/\.jpg$/', '.jpg.webp', $base) ?? $base;
        }

        return $base.'?format=webp&w=540';
    }

    public static function large(string $url): string
    {
        $base = self::stripQuery($url);
        $base = preg_replace('/\d+x\d+_c42_autohomecar__/', '1400x0_c42_autohomecar__', $base) ?? $base;

        if (! str_ends_with($base, '.webp') && str_contains($base, '.jpg')) {
            $base = preg_replace('/\.jpg$/', '.jpg.webp', $base) ?? $base;
        }

        return $base;
    }

    public static function original(string $url): string
    {
        $base = self::stripQuery($url);
        $base = preg_replace('/\d+x\d+_c42_autohomecar__/', '0x0_c42_autohomecar__', $base) ?? $base;
        $base = preg_replace('/\.webp$/', '', $base) ?? $base;

        return $base;
    }

    public static function hashFromUrl(string $url): ?string
    {
        if (preg_match('/autohomecar__([A-Za-z0-9_-]+)/', $url, $m)) {
            return $m[1];
        }

        return null;
    }

    private static function stripQuery(string $url): string
    {
        return explode('?', $url)[0];
    }
}