<?php

namespace App\Http\Controllers\Che168;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Listing;
use App\Models\Series;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ListingAdminController extends Controller
{
    public function index(Request $request): View
    {
        $query = Listing::query()->with(['photos', 'brand', 'series']);

        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }

        if ($request->filled('brand_id')) {
            $query->where('brand_id', (int) $request->input('brand_id'));
        }
        if ($request->filled('series_id')) {
            $query->where('series_id', (int) $request->input('series_id'));
        }
        if ($request->filled('fuel')) {
            $query->where('fuel_name', $request->input('fuel'));
        }
        if ($request->filled('gearbox')) {
            $query->where('gearbox', $request->input('gearbox'));
        }
        if ($request->filled('body')) {
            $query->where('body_type', $request->input('body'));
        }
        if ($request->filled('color')) {
            $query->where('color', $request->input('color'));
        }
        if ($request->filled('seats')) {
            $query->where('seats', (int) $request->input('seats'));
        }
        if ($request->filled('year_min')) {
            $query->where('reg_date', '>=', (string) $request->input('year_min'));
        }
        if ($request->filled('year_max')) {
            $query->where('reg_date', '<', (string) ((int) $request->input('year_max') + 1));
        }
        if ($request->filled('mileage_min')) {
            $query->where('mileage_km', '>=', (int) $request->input('mileage_min'));
        }
        if ($request->filled('mileage_max')) {
            $query->where('mileage_km', '<=', (int) $request->input('mileage_max'));
        }

        if ($q = trim($request->string('q')->toString())) {
            $like = '%'.$q.'%';
            $query->where(function ($b) use ($q, $like) {
                $b->where('infoid', 'like', '%'.$q.'%');
                if (config('database.default') === 'pgsql') {
                    $b->orWhere('title', 'ilike', $like);
                } else {
                    $b->orWhere('title', 'like', $like);
                }
            });
        }

        if ($request->filled('price_min')) {
            $query->where('price_usd', '>=', (float) $request->input('price_min'));
        }
        if ($request->filled('price_max')) {
            $query->where('price_usd', '<=', (float) $request->input('price_max'));
        }

        $sort = $request->input('sort', 'scraped_at');
        $dir = $request->input('dir', 'desc') === 'asc' ? 'asc' : 'desc';
        $query->orderBy(in_array($sort, ['price_usd', 'mileage_km', 'scraped_at', 'title'], true) ? $sort : 'scraped_at', $dir);

        $brandId = $request->filled('brand_id') ? (int) $request->input('brand_id') : null;

        return view('che168.listings.index', [
            'listings' => $query->paginate(24)->withQueryString(),
            'filters' => $request->only([
                'status', 'q', 'price_min', 'price_max', 'sort', 'dir',
                'brand_id', 'series_id', 'fuel', 'gearbox', 'body', 'color', 'seats',
                'year_min', 'year_max', 'mileage_min', 'mileage_max',
            ]),
            'brands' => Brand::query()->orderBy('name')->get(['brand_id', 'name']),
            'series' => $brandId
                ? Series::query()->where('brand_id', $brandId)->orderBy('name')->get(['series_id', 'name'])
                : collect(),
            'fuels' => Listing::query()->whereNotNull('fuel_name')->distinct()->orderBy('fuel_name')->pluck('fuel_name'),
            'gearboxes' => Listing::query()->whereNotNull('gearbox')->distinct()->orderBy('gearbox')->pluck('gearbox'),
            'bodies' => Listing::query()->whereNotNull('body_type')->distinct()->orderBy('body_type')->pluck('body_type'),
            'colors' => Listing::query()->whereNotNull('color')->distinct()->orderBy('color')->pluck('color'),
        ]);
    }

    public function show(Listing $listing): View
    {
        $listing->load(['photos', 'report', 'brand', 'series', 'spec']);

        return view('che168.listings.show', compact('listing'));
    }

    public function update(Request $request, Listing $listing): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:active,inactive,absent'],
            'title' => ['nullable', 'string', 'max:512'],
            'price_usd' => ['nullable', 'numeric', 'min:0'],
        ]);

        $listing->update($data);

        return back()->with('ok', 'Сохранено');
    }
}