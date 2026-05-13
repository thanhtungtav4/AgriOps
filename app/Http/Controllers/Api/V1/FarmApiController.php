<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Farm;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FarmApiController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = Farm::query();

        if (!$user->isAdmin()) {
            $query->where('id', $user->farm_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('climate_zone')) {
            $query->where('climate_zone', $request->climate_zone);
        }

        $farms = $query->orderBy('name')->get();

        return $this->success($farms);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $farm = Farm::with(['plots', 'users'])->findOrFail($id);

        if (!$user->isAdmin() && $farm->id !== $user->farm_id) {
            return $this->forbiddenError(
                'You do not have permission to access this resource.',
                [
                    'required_role' => 'admin or own farm',
                    'current_role' => $user->role,
                ]
            );
        }

        return $this->success($farm);
    }
}
