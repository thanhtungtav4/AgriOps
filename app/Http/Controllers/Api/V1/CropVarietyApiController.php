<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\CropVariety;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CropVarietyApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = CropVariety::with('crop');

        if ($request->has('crop_id')) {
            $query->where('crop_id', $request->crop_id);
        }

        $varieties = $query->orderBy('name')->get();

        return response()->json([
            'data' => $varieties,
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $variety = CropVariety::with('crop')->findOrFail($id);

        return response()->json([
            'data' => $variety,
        ]);
    }
}
