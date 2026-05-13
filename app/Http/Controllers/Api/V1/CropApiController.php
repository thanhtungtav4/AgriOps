<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Crop;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CropApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Crop::query();

        if ($request->has('group')) {
            $query->where('group', $request->group);
        }

        $crops = $query->orderBy('name')->get();

        return response()->json([
            'data' => $crops,
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $crop = Crop::with([
            'varieties',
            'standards',
            'growthStages',
            'harvestModels',
            'lossProfiles',
            'laborNorms',
        ])->findOrFail($id);

        return response()->json([
            'data' => $crop,
        ]);
    }
}
