<?php

namespace App\Services\Showcase;

use App\Models\ShowcaseCar;
use App\Models\ShowcasePhoto;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/** Фото витрины храним у себя (disk public), чтобы не зависеть от CDN донора и не светить источник. */
class PhotoStore
{
    public function upload(ShowcaseCar $car, UploadedFile $file): ShowcasePhoto
    {
        $ext = strtolower($file->guessExtension() ?: $file->getClientOriginalExtension() ?: 'jpg');
        $path = $file->storeAs("showcase/{$car->id}", Str::random(16).'.'.$ext, 'public');

        return $this->record($car, $path, null);
    }

    public function fromUrl(ShowcaseCar $car, string $url): ?ShowcasePhoto
    {
        if ($car->photos()->where('source_url', $url)->exists()) {
            return null;
        }
        try {
            $response = Http::timeout(30)->withHeaders(['User-Agent' => 'Mozilla/5.0'])->get($url);
        } catch (\Throwable) {
            return null;
        }
        if ($response->failed() || strlen($response->body()) < 500) {
            return null;
        }
        $type = (string) $response->header('Content-Type');
        $ext = str_contains($type, 'webp') ? 'webp' : (str_contains($type, 'png') ? 'png' : 'jpg');
        $path = "showcase/{$car->id}/".Str::random(16).'.'.$ext;
        Storage::disk('public')->put($path, $response->body());

        return $this->record($car, $path, $url);
    }

    private function record(ShowcaseCar $car, string $path, ?string $source): ShowcasePhoto
    {
        [$w, $h] = $this->size(Storage::disk('public')->path($path));

        return $car->photos()->create([
            'path' => $path,
            'source_url' => $source,
            'width' => $w,
            'height' => $h,
            'sort_order' => (int) $car->photos()->max('sort_order') + 1,
        ]);
    }

    /** @return array{0: int|null, 1: int|null} */
    private function size(string $file): array
    {
        $info = @getimagesize($file);

        return $info ? [min($info[0], 65535), min($info[1], 65535)] : [null, null];
    }
}
