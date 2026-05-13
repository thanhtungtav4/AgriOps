<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Plot;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlotApiController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = Plot::with('farm');

        if (!$user->isAdmin()) {
            $query->where('farm_id', $user->farm_id);
        } elseif ($request->has('farm_id')) {
            $query->where('farm_id', $request->farm_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $plots = $query->orderBy('name')->get();

        return $this->success($plots);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $plot = Plot::with(['farm', 'beds'])->findOrFail($id);

        if (!$user->isAdmin() && $plot->farm_id !== $user->farm_id) {
            return $this->forbiddenError(
                'You do not have permission to access this resource.',
                [
                    'required_role' => 'admin or own farm',
                    'current_role' => $user->role,
                ]
            );
        }

        return $this->success($plot);
    }
}
