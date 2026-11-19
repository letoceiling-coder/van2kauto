<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Catalog\SuggestionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __construct(private readonly SuggestionService $suggestions) {}

    public function suggest(Request $request): JsonResponse
    {
        $limit = (int) $request->input('limit', 8);

        return response()->json(
            $this->suggestions->suggest($request->string('q')->toString(), $limit)
        );
    }
}
