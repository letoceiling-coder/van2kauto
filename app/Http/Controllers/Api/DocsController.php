<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Catalog\EndpointCatalog;
use Illuminate\View\View;

class DocsController extends Controller
{
    public function show(): View
    {
        $base = rtrim(url('/api/v1'), '/');

        return view('api.docs', [
            'endpoints' => EndpointCatalog::all(),
            'base' => $base,
            'plain' => EndpointCatalog::plain($base),
        ]);
    }
}
