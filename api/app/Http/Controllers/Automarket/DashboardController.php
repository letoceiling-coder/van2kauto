<?php

namespace App\Http\Controllers\Automarket;

use App\Http\Controllers\Controller;
use App\Models\AuctionLot;
use App\Models\AuctionRun;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $activeRun = AuctionRun::query()
            ->whereIn('status', ['pending', 'running', 'needs_captcha', 'needs_auth'])
            ->latest('id')
            ->first();

        return view('automarket.dashboard', [
            'stats' => [
                'active' => AuctionLot::query()->where('status', 'active')->count(),
                'absent' => AuctionLot::query()->where('status', 'absent')->count(),
                'total' => AuctionLot::query()->count(),
            ],
            'activeRun' => $activeRun,
            'recentRuns' => AuctionRun::query()->latest('id')->limit(10)->get(),
        ]);
    }
}
