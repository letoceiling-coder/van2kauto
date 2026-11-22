<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(300)->by($request->ip());
        });
        // Заявки: защита от флуда формы — 5 в минуту и 30 в час с одного IP.
        RateLimiter::for('chat', fn (Request $request) => Limit::perMinute(20)->by($request->ip()));
        RateLimiter::for('chat-poll', fn (Request $request) => Limit::perMinute(40)->by($request->ip()));
        RateLimiter::for('leads', fn (Request $request) => [
            Limit::perMinute(5)->by('m'.$request->ip()),
            Limit::perHour(30)->by('h'.$request->ip()),
        ]);
    }
}
