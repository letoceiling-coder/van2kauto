<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Services\Catalog\ListingFilter;
use App\Services\Catalog\ListingPresenter;
use App\Services\Catalog\PageWindow;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ListingController extends Controller
{
    public function __construct(
        private readonly ListingFilter $filters,
        private readonly ListingPresenter $presenter,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $page = PageWindow::from($request);
        $query = $this->filters->sort($this->filters->query($request), $request);
        $total = (clone $query)->count();

        $items = $query
            ->with(['brand', 'series', 'spec'])
            ->withCount('photos')
            ->with(['photos' => fn ($photos) => $photos->where('sort_order', 0)])
            ->skip($page->offset)
            ->take($page->limit)
            ->get()
            ->map(fn (Listing $listing) => $this->presenter->card($listing))
            ->values();

        return response()->json([
            'data' => $items,
            'meta' => $page->meta($total),
        ]);
    }

    public function show(Request $request, Listing $listing): JsonResponse
    {
        $listing->load(['brand', 'series', 'spec', 'photos', 'report']);

        return response()->json([
            'data' => $this->presenter->detail($listing, $request->boolean('include_raw')),
        ]);
    }
}
