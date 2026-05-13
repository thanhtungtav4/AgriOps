<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Crop;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CropApiController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $query = Crop::query();

        if ($request->has('group')) {
            $query->where('group', $request->group);
        }

        $crops = $query->orderBy('name')->get();

        return $this->success($crops);
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

        return $this->success($crop);
    }
}
