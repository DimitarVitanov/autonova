<?php

namespace App\Http\Controllers;

use App\Support\MarketplaceOptions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    /** Makes for a category (JSON, for dependent selects). */
    public function makes(Request $request): JsonResponse
    {
        return response()->json(MarketplaceOptions::makes($request->input('category')));
    }

    /** Models for a make (JSON, for dependent selects). */
    public function models(Request $request): JsonResponse
    {
        return response()->json(MarketplaceOptions::models((int) $request->input('make_id')));
    }

    /** Versions / trims for a model (JSON, for dependent selects). */
    public function versions(Request $request): JsonResponse
    {
        return response()->json(MarketplaceOptions::versions((int) $request->input('car_model_id')));
    }
}
