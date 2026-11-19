<?php

namespace App\Services\Catalog;

use App\Models\Brand;
use App\Models\Listing;
use App\Models\Series;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ListingFilter
{
    /** @param  list<string>  $except */
    public function query(Request $request, array $except = []): Builder
    {
        $query = Listing::query();
        $skip = array_flip($except);

        $status = (string) $request->input('status', 'active');
        if (! isset($skip['status']) && ! in_array($status, ['any', 'all', ''], true)) {
            $query->where('status', $status);
        }

        if (! isset($skip['brand'])) {
            $this->whereIds($query, 'brand_id', $this->list($request, 'brand_id', 'category_id'));
        }
        if (! isset($skip['model'])) {
            $this->whereIds($query, 'series_id', $this->list($request, 'series_id', 'model_id'));
        }
        if (! isset($skip['spec'])) {
            $this->whereIds($query, 'spec_id', $this->list($request, 'spec_id'));
        }

        if (! isset($skip['fuel'])) {
            $this->whereStrings($query, 'fuel_name', $this->list($request, 'fuel', 'fuel_name'));
        }
        if (! isset($skip['gearbox'])) {
            $this->whereStrings($query, 'gearbox', $this->list($request, 'gearbox'));
        }
        if (! isset($skip['body'])) {
            $this->whereStrings($query, 'body_type', $this->list($request, 'body', 'body_type'));
        }
        if (! isset($skip['color'])) {
            $this->whereStrings($query, 'color', $this->list($request, 'color'));
        }
        if (! isset($skip['steering'])) {
            $this->whereStrings($query, 'steering', $this->list($request, 'steering'));
        }
        if (! isset($skip['drive'])) {
            $this->whereStrings($query, 'driving_mode', $this->list($request, 'driving_mode', 'drive'));
        }
        if (! isset($skip['class'])) {
            $this->whereStrings($query, 'class_level', $this->list($request, 'class_level', 'class'));
        }
        if (! isset($skip['city'])) {
            $this->whereStrings($query, 'city_slug', $this->list($request, 'city', 'city_slug'));
        }

        if (! isset($skip['seats']) && $request->filled('seats')) {
            $query->where('seats', (int) $request->input('seats'));
        }
        if (! isset($skip['doors']) && $request->filled('doors')) {
            $query->where('doors', (int) $request->input('doors'));
        }
        if (! isset($skip['price'])) {
            if ($request->filled('price_min')) {
                $query->where('price_usd', '>=', (float) $request->input('price_min'));
            }
            if ($request->filled('price_max')) {
                $query->where('price_usd', '<=', (float) $request->input('price_max'));
            }
        }
        if (! isset($skip['mileage'])) {
            if ($request->filled('mileage_min')) {
                $query->where('mileage_km', '>=', (int) $request->input('mileage_min'));
            }
            if ($request->filled('mileage_max')) {
                $query->where('mileage_km', '<=', (int) $request->input('mileage_max'));
            }
        }
        if (! isset($skip['year'])) {
            if ($request->filled('year_min')) {
                $query->where('reg_date', '>=', (string) $request->input('year_min'));
            }
            if ($request->filled('year_max')) {
                $query->where('reg_date', '<', (string) ((int) $request->input('year_max') + 1));
            }
        }
        if (! isset($skip['cert']) && $request->filled('cert_status')) {
            $query->where('cert_status', (int) $request->input('cert_status'));
        }

        if (! isset($skip['q']) && ($q = trim($request->string('q')->toString())) !== '') {
            $this->applySearch($query, $q);
        }

        return $query;
    }

    public function sort(Builder $query, Request $request): Builder
    {
        $sort = (string) $request->input('sort', 'scraped_at');
        $dir = $request->input('dir', 'desc') === 'asc' ? 'asc' : 'desc';
        $column = match ($sort) {
            'price', 'price_usd' => 'price_usd',
            'mileage', 'mileage_km' => 'mileage_km',
            'year', 'reg_date' => 'reg_date',
            'title' => 'title',
            default => 'scraped_at',
        };

        if ($request->filled('q')) {
            $needle = mb_strtolower(trim($request->string('q')->toString()));
            $query->orderByRaw(
                'case when lower(coalesce(title, \'\')) like ? then 0 when lower(coalesce(title, \'\')) like ? then 1 else 2 end',
                [$needle.'%', '%'.$needle.'%']
            );
        }

        return $query->orderBy($column, $dir)->orderBy('infoid');
    }

    /** @return array<string, mixed> */
    public function facets(Request $request): array
    {
        return [
            'categories' => $this->brandFacet($request),
            'models' => $this->modelFacet($request),
            'fuels' => $this->valueFacet($request, 'fuel_name', ['fuel']),
            'gearboxes' => $this->valueFacet($request, 'gearbox', ['gearbox']),
            'bodies' => $this->valueFacet($request, 'body_type', ['body']),
            'colors' => $this->valueFacet($request, 'color', ['color']),
            'drives' => $this->valueFacet($request, 'driving_mode', ['drive']),
            'classes' => $this->valueFacet($request, 'class_level', ['class']),
            'cities' => $this->valueFacet($request, 'city_slug', ['city']),
            'years' => $this->yearFacet($request),
            'price' => $this->range($request, 'price_usd', ['price']),
            'mileage' => $this->range($request, 'mileage_km', ['mileage']),
        ];
    }

    private function applySearch(Builder $query, string $q): void
    {
        $tokens = preg_split('/\s+/u', $q, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        foreach ($tokens as $token) {
            $like = '%'.$this->escapeLike($token).'%';
            $op = $this->likeOperator();
            $query->where(function (Builder $inner) use ($like, $op, $token) {
                foreach (['title', 'fuel_name', 'gearbox', 'body_type', 'color', 'city_slug', 'engine', 'class_level', 'driving_mode'] as $column) {
                    $inner->orWhere($column, $op, $like);
                }
                if (ctype_digit($token)) {
                    $inner->orWhere('infoid', (int) $token);
                }
                $inner->orWhereHas('brand', fn (Builder $b) => $b->where('name', $op, $like));
                $inner->orWhereHas('series', fn (Builder $b) => $b->where('name', $op, $like));
                $inner->orWhereHas('spec', fn (Builder $b) => $b->where('name', $op, $like));
            });
        }
    }

    /** @param  list<int|string>  $values */
    private function whereIds(Builder $query, string $column, array $values): void
    {
        $ids = array_values(array_filter(array_map('intval', $values)));
        if ($ids !== []) {
            $query->whereIn($column, $ids);
        }
    }

    /** @param  list<int|string>  $values */
    private function whereStrings(Builder $query, string $column, array $values): void
    {
        $values = array_values(array_filter($values, fn ($v) => $v !== '' && $v !== null));
        if ($values !== []) {
            $query->whereIn($column, $values);
        }
    }

    /** @return list<string> */
    private function list(Request $request, string ...$keys): array
    {
        $values = [];
        foreach ($keys as $key) {
            if (! $request->exists($key)) {
                continue;
            }
            $raw = $request->input($key);
            $parts = is_array($raw) ? $raw : explode(',', (string) $raw);
            foreach ($parts as $part) {
                $part = trim((string) $part);
                if ($part !== '') {
                    $values[] = $part;
                }
            }
        }

        return array_values(array_unique($values));
    }

    /** @param  list<string>  $except */
    private function brandFacet(Request $request): array
    {
        $counts = $this->query($request, ['brand'])
            ->whereNotNull('brand_id')
            ->select('brand_id', DB::raw('count(*) as aggregate'))
            ->groupBy('brand_id')
            ->pluck('aggregate', 'brand_id');

        if ($counts->isEmpty()) {
            return [];
        }

        return Brand::query()
            ->whereIn('brand_id', $counts->keys())
            ->orderBy('name')
            ->get(['brand_id', 'name', 'icon_url'])
            ->map(fn (Brand $brand) => [
                'id' => $brand->brand_id,
                'name' => $brand->name,
                'icon_url' => $brand->icon_url,
                'count' => (int) $counts[$brand->brand_id],
            ])
            ->all();
    }

    /** @param  list<string>  $except */
    private function modelFacet(Request $request): array
    {
        $counts = $this->query($request, ['model'])
            ->whereNotNull('series_id')
            ->select('series_id', DB::raw('count(*) as aggregate'))
            ->groupBy('series_id')
            ->pluck('aggregate', 'series_id');

        if ($counts->isEmpty()) {
            return [];
        }

        return Series::query()
            ->with('brand:brand_id,name')
            ->whereIn('series_id', $counts->keys())
            ->orderBy('name')
            ->get()
            ->map(fn (Series $series) => [
                'id' => $series->series_id,
                'name' => $series->name,
                'category_id' => $series->brand_id,
                'category' => $series->brand?->name,
                'count' => (int) $counts[$series->series_id],
            ])
            ->all();
    }

    /** @param  list<string>  $except */
    private function valueFacet(Request $request, string $column, array $except): array
    {
        return $this->query($request, $except)
            ->whereNotNull($column)
            ->where($column, '!=', '')
            ->select($column, DB::raw('count(*) as aggregate'))
            ->groupBy($column)
            ->orderBy($column)
            ->get()
            ->map(fn ($row) => [
                'value' => $row->{$column},
                'count' => (int) $row->aggregate,
            ])
            ->all();
    }

    /** @return list<array{value: string, count: int}> */
    private function yearFacet(Request $request): array
    {
        $expr = DB::connection()->getDriverName() === 'pgsql'
            ? 'substring(reg_date from 1 for 4)'
            : 'substr(reg_date, 1, 4)';

        return $this->query($request, ['year'])
            ->whereNotNull('reg_date')
            ->selectRaw($expr.' as year_value, count(*) as aggregate')
            ->groupByRaw($expr)
            ->orderByDesc('year_value')
            ->get()
            ->map(fn ($row) => [
                'value' => (string) $row->year_value,
                'count' => (int) $row->aggregate,
            ])
            ->all();
    }

    /** @param  list<string>  $except
     * @return array{min: float|int|null, max: float|int|null}
     */
    private function range(Request $request, string $column, array $except): array
    {
        $row = $this->query($request, $except)
            ->selectRaw('min('.$column.') as min_value, max('.$column.') as max_value')
            ->first();

        return [
            'min' => $row?->min_value !== null ? (float) $row->min_value : null,
            'max' => $row?->max_value !== null ? (float) $row->max_value : null,
        ];
    }

    private function likeOperator(): string
    {
        return DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
    }

    private function escapeLike(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }
}
