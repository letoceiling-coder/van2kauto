<?php

namespace App\Services\Che168;

use App\Models\ListingPhoto;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PhotoDownloadService
{
    public function downloadForInfoid(int $infoid, bool $originals = true, bool $previews = false): int
    {
        $photos = ListingPhoto::query()->where('infoid', $infoid)->orderBy('sort_order')->get();
        $downloaded = 0;

        foreach ($photos as $photo) {
            if ($originals && $photo->original_url) {
                $path = $this->storeUrl($infoid, $photo->sort_order, (string) $photo->original_url, 'jpg');
                if ($path) {
                    $photo->file_path = $path;
                    $downloaded++;
                }
            }

            if ($previews && $photo->preview_url) {
                $this->storeUrl($infoid, $photo->sort_order, (string) $photo->preview_url, 'webp', 'preview');
            }

            $photo->save();
        }

        return $downloaded;
    }

    private function storeUrl(int $infoid, int $sort, string $url, string $ext, string $prefix = 'original'): ?string
    {
        $hash = ImageUrlHelper::hashFromUrl($url) ?? Str::random(8);
        $relative = "che168/{$infoid}/{$prefix}_{$sort}_{$hash}.{$ext}";

        try {
            $response = Http::timeout(60)
                ->withHeaders(['User-Agent' => 'Mozilla/5.0'])
                ->get($url);

            if ($response->failed()) {
                return null;
            }

            Storage::disk('local')->put($relative, $response->body());

            return $relative;
        } catch (\Throwable) {
            return null;
        }
    }
}