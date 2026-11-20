<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Catalog\EndpointCatalog;
use Illuminate\Http\JsonResponse;

class MetaController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'name' => 'ВАН23АВТО Che168 API',
            'version' => '1',
            'docs' => url('/api/docs'),
            'openapi' => url('/api/v1/openapi.json'),
            'endpoints' => array_map(fn (array $endpoint) => [
                'method' => $endpoint['method'],
                'path' => $endpoint['path'],
                'title' => $endpoint['title'],
            ], EndpointCatalog::all()),
        ]);
    }

    public function openapi(): JsonResponse
    {
        return response()->json(EndpointCatalog::openapi());
    }
}
