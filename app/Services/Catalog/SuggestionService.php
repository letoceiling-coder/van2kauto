<?php

namespace App\Services\Catalog;

use App\Models\Brand;
use App\Models\Listing;
use App\Models\Series;
use Illuminate\Support\Facades\DB;

class SuggestionService
{
    public function __construct(private readonly ListingPresenter $listings) {}

    /** @return array<string, mixed> */
    public function suggest(string $q, int $limit = 8): array
    {
        $q = trim($q);
        $limit = min(20, max(1, $limit));

        if (mb_strlen($q) < 1) {
            return [
                'query' => $q,
                'categories' => [],
                'models' => [],
                'products' => [],
            ];
        }

        $like = '%'.$this->escapeLike($q).'%';
        $prefix = $this->escapeLike($q).'%';
        $op = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';

        $categories = Brand::query()
            ->withCount('listings')
            ->where('name', $op, $like)
            ->orderByRaw('case when name '.$op.' ? then 0 else 1 end', [$prefix])
            ->orderBy('name')
            ->limit($limit)
            ->get()
            ->map(fn (Brand $brand) => [
                'type' => 'category',
                'id' => $brand->brand_id,
                'name' => $brand->name,
                'listings_count' => $brand->listings_count,
            ])
            ->all();

        $models = Series::query()
            ->with('brand:brand_id,name')
            ->withCount('listings')
            ->where('name', $op, $like)
            ->orderByRaw('case when name '.$op.' ? then 0 else 1 end', [$prefix])
            ->orderBy('name')
            ->limit($limit)
            ->get()
            ->map(fn (Series $series) => [
                'type' => 'model',
                'id' => $series->series_id,
                'name' => $series->name,
                'category_id' => $series->brand_id,
                'category' => $series->brand?->name,
                'listings_count' => $series->listings_count,
            ])
            ->all();

        $products = Listing::query()
            ->with(['brand', 'series', 'photos' => fn ($query) => $query->where('sort_order', 0)])
            ->where('status', 'active')
            ->where(function ($inner) use ($op, $like, $q) {
                $inner->where('title', $op, $like);
                if (ctype_digit($q)) {
                    $inner->orWhere('infoid', (int) $q);
                }
            })
            ->orderByRaw('case when lower(coalesce(title, \'\')) like ? then 0 else 1 end', [mb_strtolower($q).'%'])
            ->orderByDesc('scraped_at')
            ->limit($limit)
            ->get()
            ->map(fn (Listing $listing) => [
                'type' => 'product',
                'infoid' => (int) $listing->infoid,
                'title' => $listing->title,
                'price_usd' => $listing->price_usd !== null ? (float) $listing->price_usd : null,
                'category' => $listing->brand?->name,
                'model' => $listing->series?->name,
                'photo' => $listing->photos->first()?->preview_url,
            ])
            ->all();

        return [
            'query' => $q,
            'categories' => $categories,
            'models' => $models,
            'products' => $products,
        ];
    }

    private function escapeLike(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }
}
