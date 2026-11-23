<?php

namespace App\Http\Controllers\Automarket;

use App\Http\Controllers\Controller;
use App\Models\AuctionLot;
use App\Models\AuctionMaker;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LotController extends Controller
{
    public function index(Request $request): View
    {
        $query = AuctionLot::query()->with(['photos', 'maker']);

        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }
        if ($request->filled('maker_id')) {
            $query->where('maker_id', (int) $request->input('maker_id'));
        }
        if ($request->filled('year_min')) {
            $query->where('year', '>=', (int) $request->input('year_min'));
        }
        if ($request->filled('year_max')) {
            $query->where('year', '<=', (int) $request->input('year_max'));
        }
        if ($request->filled('price_min')) {
            $query->where('start_price_jpy', '>=', (int) $request->input('price_min'));
        }
        if ($request->filled('price_max')) {
            $query->where('start_price_jpy', '<=', (int) $request->input('price_max'));
        }
        if ($request->filled('mileage_min')) {
            $query->where('mileage_km', '>=', (int) $request->input('mileage_min'));
        }
        if ($request->filled('mileage_max')) {
            $query->where('mileage_km', '<=', (int) $request->input('mileage_max'));
        }
        if ($auction = trim($request->string('auction')->toString())) {
            $like = '%'.$auction.'%';
            $query->where('auction_name', config('database.default') === 'pgsql' ? 'ilike' : 'like', $like);
        }
        if ($q = trim($request->string('q')->toString())) {
            $like = '%'.$q.'%';
            $op = config('database.default') === 'pgsql' ? 'ilike' : 'like';
            $query->where(function ($inner) use ($q, $like, $op) {
                $inner->where('lot_id', 'like', '%'.$q.'%')
                    ->orWhere('bid', $op, $like)
                    ->orWhere('maker_name', $op, $like)
                    ->orWhere('model_name', $op, $like)
                    ->orWhere('grade', $op, $like);
            });
        }

        return view('automarket.lots.index', [
            'lots' => $query->orderByDesc('auction_at')->orderByDesc('lot_id')->paginate(24)->withQueryString(),
            'filters' => $request->only(['status', 'maker_id', 'year_min', 'year_max', 'price_min', 'price_max', 'mileage_min', 'mileage_max', 'auction', 'q']),
            'makers' => AuctionMaker::query()->orderBy('name')->get(['maker_id', 'name']),
        ]);
    }

    public function show(AuctionLot $lot): View
    {
        $lot->load(['photos', 'maker', 'model']);

        return view('automarket.lots.show', compact('lot'));
    }

    public function update(Request $request, AuctionLot $lot): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:active,inactive,absent'],
            'grade' => ['nullable', 'string', 'max:255'],
            'start_price_jpy' => ['nullable', 'integer', 'min:0'],
        ]);
        $lot->update($data);

        return back()->with('ok', 'Сохранено');
    }
}
