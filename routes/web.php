<?php

use App\Http\Controllers\Api\DocsController;
use App\Http\Controllers\Automarket\DashboardController as AutomarketDashboardController;
use App\Http\Controllers\Automarket\LotController;
use App\Http\Controllers\Automarket\ParseRunController as AutomarketParseRunController;
use App\Http\Controllers\Automarket\ProxyController;
use App\Http\Controllers\Automarket\SessionController as AutomarketSessionController;
use App\Http\Controllers\Che168\AuthController;
use App\Http\Controllers\Che168\DashboardController;
use App\Http\Controllers\Che168\ListingAdminController;
use App\Http\Controllers\Che168\ParseRunController;
use App\Http\Controllers\Che168GateController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/che168');

Route::get('/api/docs', [DocsController::class, 'show'])->name('api.docs');

Route::controller(Che168GateController::class)->group(function () {
    Route::get('/che168/captcha', 'showPublic')->name('che168.gate.public');
    Route::get('/che168/gate', 'show')->name('che168.gate.show');
    Route::get('/che168/gate/proxy', 'proxy')->name('che168.gate.proxy');
    Route::post('/che168/gate/store', 'store')->name('che168.gate.store');
});

Route::prefix('che168')->name('che168.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    });

    Route::middleware('auth')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::post('/parse/start', [ParseRunController::class, 'start'])->name('parse.start');
        Route::get('/parse/active', [ParseRunController::class, 'active'])->name('parse.active');
        Route::get('/parse/runs/{run}', [ParseRunController::class, 'show'])->name('parse.show');
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

        Route::get('/listings', [ListingAdminController::class, 'index'])->name('listings.index');
        Route::get('/listings/{listing}', [ListingAdminController::class, 'show'])->name('listings.show');
        Route::put('/listings/{listing}', [ListingAdminController::class, 'update'])->name('listings.update');
    });
});

Route::prefix('a-automarket')->name('automarket.')->middleware('auth')->group(function () {
    Route::get('/', [AutomarketDashboardController::class, 'index'])->name('dashboard');
    Route::get('/session', [AutomarketSessionController::class, 'show'])->name('session');
    Route::post('/session', [AutomarketSessionController::class, 'store'])->name('session.store');
    Route::post('/parse/start', [AutomarketParseRunController::class, 'start'])->name('parse.start');
    Route::get('/parse/active', [AutomarketParseRunController::class, 'active'])->name('parse.active');
    Route::get('/parse/runs/{run}', [AutomarketParseRunController::class, 'show'])->name('parse.show');
    Route::get('/lots', [LotController::class, 'index'])->name('lots.index');
    Route::get('/lots/{lot}', [LotController::class, 'show'])->name('lots.show');
    Route::put('/lots/{lot}', [LotController::class, 'update'])->name('lots.update');
    Route::get('/proxies', [ProxyController::class, 'index'])->name('proxies.index');
    Route::post('/proxies', [ProxyController::class, 'store'])->name('proxies.store');
    Route::post('/proxies/{proxy}/toggle', [ProxyController::class, 'toggle'])->name('proxies.toggle');
    Route::delete('/proxies/{proxy}', [ProxyController::class, 'destroy'])->name('proxies.destroy');
});