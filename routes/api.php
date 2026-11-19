<?php

use App\Http\Controllers\Api\V1\CatalogController;
use App\Http\Controllers\Api\V1\ListingController;
use App\Http\Controllers\Api\V1\MetaController;
use App\Http\Controllers\Api\V1\SearchController;
use App\Http\Controllers\Api\TelegramWebhookController;
use App\Http\Controllers\Api\V1\LeadController;
use App\Http\Controllers\Api\V1\ShowcaseController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/', [MetaController::class, 'index']);
    Route::get('/openapi.json', [MetaController::class, 'openapi']);

    Route::get('/listings', [ListingController::class, 'index']);
    Route::get('/listings/{listing}', [ListingController::class, 'show']);

    Route::get('/search/suggest', [SearchController::class, 'suggest']);

    Route::get('/categories', [CatalogController::class, 'categories']);
    Route::get('/models', [CatalogController::class, 'models']);
    Route::get('/models/{series}', [CatalogController::class, 'showModel']);
    Route::get('/specs', [CatalogController::class, 'specs']);
    Route::get('/facets', [CatalogController::class, 'facets']);
    Route::get('/properties', [CatalogController::class, 'properties']);

    Route::get('/showcase', [ShowcaseController::class, 'index']);
    Route::get('/showcase/{slug}', [ShowcaseController::class, 'show']);
    Route::post('/leads', [LeadController::class, 'store'])->middleware('throttle:leads');
});

Route::post('/telegram/webhook', TelegramWebhookController::class)->middleware('throttle:120,1');
