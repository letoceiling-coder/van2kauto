<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ShowcaseCar;
use App\Services\Showcase\ShowcasePresenter;
use App\Support\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * Опубликованная витрина для сайта.
 * Кэш: ответ собирается один раз на версию витрины (версия растёт при любом изменении карточки/фото),
 * клиенту — ETag + Cache-Control с stale-while-revalidate; повторный запрос с If-None-Match → 304.
 */
class ShowcaseController extends Controller
{
    public function __construct(private readonly ShowcasePresenter $presenter) {}

    public function index(Request $request): Response
    {
        $version = Settings::int('showcase_version', 1);
        $payload = Cache::remember("showcase:list:v{$version}", 86400, function () use ($version) {
            $cars = ShowcaseCar::query()
                ->where('is_published', true)
                ->with('photos')
                ->orderBy('sort_order')
                ->orderByDesc('id')
                ->get()
                ->map(fn (ShowcaseCar $car) => $this->presenter->car($car))
                ->values()
                ->all();

            return ['data' => $cars, 'meta' => [
                'total' => count($cars),
                'version' => $version,
                'generated_at' => now()->toIso8601String(),
                // Правило «проходной» из настроек — сайт показывает те же цифры, что и в админке.
                'utilization' => [
                    'max_hp' => Settings::int('utilization_max_hp', 160),
                    'min_age' => Settings::int('utilization_min_age', 3),
                    'max_age' => Settings::int('utilization_max_age', 5),
                ],
                'company' => Settings::company(),
            ]];
        });

        return $this->cached($request, $payload, "list-{$version}");
    }

    public function show(Request $request, string $slug): Response
    {
        $version = Settings::int('showcase_version', 1);
        $payload = Cache::remember("showcase:car:{$slug}:v{$version}", 86400, function () use ($slug) {
            $car = ShowcaseCar::query()->where('slug', $slug)->where('is_published', true)->with('photos')->first();

            return $car ? ['data' => $this->presenter->car($car)] : null;
        });

        if ($payload === null) {
            return response()->json(['message' => 'Карточка не найдена'], 404);
        }

        return $this->cached($request, $payload, "car-{$slug}-{$version}");
    }

    private function cached(Request $request, array $payload, string $tag): Response
    {
        $etag = '"'.md5($tag.json_encode($payload['data'])).'"';
        $headers = [
            'ETag' => $etag,
            'Cache-Control' => 'public, max-age=60, stale-while-revalidate=600',
            'Vary' => 'Accept-Encoding',
        ];

        if (in_array($etag, array_map('trim', explode(',', (string) $request->header('If-None-Match'))), true)) {
            return response('', 304, $headers);
        }

        return response()->json($payload, 200, $headers, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
