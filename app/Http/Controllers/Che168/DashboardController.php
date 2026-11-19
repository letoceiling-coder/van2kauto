<?php

namespace App\Http\Controllers\Che168;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\ScrapeRun;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $activeRun = ScrapeRun::query()
            ->whereIn('status', ['pending', 'running', 'needs_captcha'])
            ->latest('id')
            ->first();

        return view('che168.dashboard', [
            'stats' => [
                'listings_active' => Listing::query()->where('status', 'active')->count(),
                'listings_absent' => Listing::query()->where('status', 'absent')->count(),
                'listings_total' => Listing::query()->count(),
            ],
            'activeRun' => $activeRun,
            'recentRuns' => ScrapeRun::query()->latest('id')->limit(10)->get(),
        ]);
    }
}