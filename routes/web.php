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
use App\Http\Controllers\Admin\BotAdminController;
use App\Http\Controllers\Admin\LeadAdminController;
use App\Http\Controllers\Admin\SettingsAdminController;
use App\Http\Controllers\Admin\ShowcaseAdminController;
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
Route::prefix('che168')->name('admin.')->middleware('auth')->group(function () {
    Route::get('/showcase', [ShowcaseAdminController::class, 'index'])->name('showcase.index');
    Route::get('/showcase/create', [ShowcaseAdminController::class, 'create'])->name('showcase.create');
    Route::post('/showcase', [ShowcaseAdminController::class, 'store'])->name('showcase.store');
    Route::post('/showcase/bulk', [ShowcaseAdminController::class, 'bulk'])->name('showcase.bulk');
    Route::post('/showcase/from-listings', [ShowcaseAdminController::class, 'bulkFromListings'])->name('showcase.from-listings');
    Route::post('/showcase/from-listing/{listing}', [ShowcaseAdminController::class, 'fromListing'])->name('showcase.from-listing');
    Route::get('/showcase/{car}/edit', [ShowcaseAdminController::class, 'edit'])->name('showcase.edit');
    Route::put('/showcase/{car}', [ShowcaseAdminController::class, 'update'])->name('showcase.update');
    Route::delete('/showcase/{car}', [ShowcaseAdminController::class, 'destroy'])->name('showcase.destroy');
    Route::post('/showcase/{car}/duplicate', [ShowcaseAdminController::class, 'duplicate'])->name('showcase.duplicate');
    Route::post('/showcase/{car}/publish', [ShowcaseAdminController::class, 'togglePublish'])->name('showcase.publish');
    Route::post('/showcase/{car}/fill', [ShowcaseAdminController::class, 'fillFromListing'])->name('showcase.fill');
    Route::post('/showcase/{car}/photos', [ShowcaseAdminController::class, 'uploadPhotos'])->name('showcase.photos.upload');
    Route::put('/showcase/{car}/photos', [ShowcaseAdminController::class, 'updatePhotos'])->name('showcase.photos.update');

    Route::get('/leads', [LeadAdminController::class, 'index'])->name('leads.index');
    Route::get('/leads/{lead}', [LeadAdminController::class, 'show'])->name('leads.show');
    Route::put('/leads/{lead}', [LeadAdminController::class, 'update'])->name('leads.update');
    Route::post('/leads/{lead}/renotify', [LeadAdminController::class, 'renotify'])->name('leads.renotify');

    Route::get('/bot', [BotAdminController::class, 'index'])->name('bot.index');
    Route::post('/bot/broadcasts', [BotAdminController::class, 'storeBroadcast'])->name('bot.broadcasts.store');
    Route::post('/bot/broadcasts/{broadcast}/cancel', [BotAdminController::class, 'cancelBroadcast'])->name('bot.broadcasts.cancel');

    Route::get('/settings', [SettingsAdminController::class, 'show'])->name('settings.show');
    Route::put('/settings', [SettingsAdminController::class, 'update'])->name('settings.update');
    Route::post('/settings/bot-test', [SettingsAdminController::class, 'testBot'])->name('settings.bot-test');
    Route::post('/settings/webhook', [SettingsAdminController::class, 'setWebhook'])->name('settings.webhook');
    Route::delete('/settings/webhook', [SettingsAdminController::class, 'deleteWebhook'])->name('settings.webhook.delete');
    Route::post('/settings/rebuild', [SettingsAdminController::class, 'rebuild'])->name('settings.rebuild');
});
