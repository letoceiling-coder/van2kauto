<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Listing;
use App\Models\Series;
use App\Models\Spec;
use App\Services\Catalog\ListingFilter;
use App\Services\Catalog\PageWindow;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CatalogController extends Controller
{
    public function __construct(private readonly ListingFilter $filters) {}

    public function categories(Request $request): JsonResponse
    {
        $page = PageWindow::from($request, 50, 200);
        $query = $this->named(Brand::query()->withCount('listings'), $request);

        return response()->json([
            'data' => $query->clone()->skip($page->offset)->take($page->limit)->get()->map(fn (Brand $brand) => [
                'id' => $brand->brand_id,
                'name' => $brand->name,
                'py' => $brand->py,
                'icon_url' => $brand->icon_url,
                'listings_count' => $brand->listings_count,
            ])->values(),
            'meta' => $page->meta((clone $query)->count()),
        ]);
    }

    public function models(Request $request): JsonResponse
    {
        $page = PageWindow::from($request, 50, 200);
        $query = Series::query()->with('brand:brand_id,name')->withCount('listings')->orderBy('name');

        $categoryId = $request->input('category_id', $request->input('brand_id'));
        if ($categoryId) {
            $query->where('brand_id', (int) $categoryId);
        }
        $query = $this->named($query, $request);

        return response()->json([
            'data' => $query->clone()->skip($page->offset)->take($page->limit)->get()->map(fn (Series $series) => $this->modelPayload($series))->values(),
            'meta' => $page->meta((clone $query)->count()),
        ]);
    }

    public function showModel(Series $series): JsonResponse
    {
        $series->load(['brand:brand_id,name', 'specs']);
        $series->loadCount('listings');
        $payload = $this->modelPayload($series);
        $payload['specs'] = $series->specs->map(fn (Spec $spec) => [
            'id' => $spec->spec_id,
            'name' => $spec->name,
            'year_label' => $spec->year_label,
        ])->values();

        return response()->json(['data' => $payload]);
    }

    public function specs(Request $request): JsonResponse
    {
        $page = PageWindow::from($request, 50, 200);
        $query = Spec::query()->with('series.brand')->orderBy('name');
        $modelId = $request->input('model_id', $request->input('series_id'));
        if ($modelId) {
            $query->where('series_id', (int) $modelId);
        }
        if ($request->filled('category_id')) {
            $query->whereHas('series', fn (Builder $series) => $series->where('brand_id', (int) $request->input('category_id')));
        }
        $query = $this->named($query, $request);

        return response()->json([
            'data' => $query->clone()->skip($page->offset)->take($page->limit)->get()->map(fn (Spec $spec) => [
                'id' => $spec->spec_id,
                'name' => $spec->name,
                'year_label' => $spec->year_label,
                'model_id' => $spec->series_id,
                'model' => $spec->series?->name,
                'category_id' => $spec->series?->brand_id,
                'category' => $spec->series?->brand?->name,
            ])->values(),
            'meta' => $page->meta((clone $query)->count()),
        ]);
    }

    public function facets(Request $request): JsonResponse
    {
        return response()->json(['data' => $this->filters->facets($request)]);
    }

    public function properties(): JsonResponse
    {
        $columns = [
            'fuel' => ['title' => 'Топливо', 'column' => 'fuel_name'],
            'gearbox' => ['title' => 'КПП', 'column' => 'gearbox'],
            'body_type' => ['title' => 'Кузов', 'column' => 'body_type'],
            'color' => ['title' => 'Цвет', 'column' => 'color'],
            'driving_mode' => ['title' => 'Привод', 'column' => 'driving_mode'],
            'steering' => ['title' => 'Руль', 'column' => 'steering'],
            'class_level' => ['title' => 'Класс', 'column' => 'class_level'],
            'city' => ['title' => 'Город', 'column' => 'city_slug'],
        ];

        $data = [];
        foreach ($columns as $key => $meta) {
            $data[] = [
                'key' => $key,
                'title' => $meta['title'],
                'values' => Listing::query()
                    ->where('status', 'active')
                    ->whereNotNull($meta['column'])
                    ->where($meta['column'], '!=', '')
                    ->distinct()
                    ->orderBy($meta['column'])
                    ->pluck($meta['column'])
                    ->values(),
            ];
        }

        return response()->json(['data' => $data]);
    }

    /** @return array<string, mixed> */
    private function modelPayload(Series $series): array
    {
        return [
            'id' => $series->series_id,
            'name' => $series->name,
            'icon_url' => $series->icon_url,
            'category_id' => $series->brand_id,
            'category' => $series->brand?->name,
            'listings_count' => $series->listings_count ?? null,
        ];
    }

    private function named(Builder $query, Request $request): Builder
    {
        if ($q = trim($request->string('q')->toString())) {
            $op = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
            $query->where('name', $op, '%'.addcslashes($q, '\\%_').'%');
        }

        return $query->orderBy('name');
    }
}
