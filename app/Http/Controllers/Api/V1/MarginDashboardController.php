<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Services\CostingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MarginDashboardController extends Controller
{
    use ApiResponse;

    public function __invoke(Request $request, CostingService $costingService): JsonResponse
    {
        $user = $request->user();
        $farmId = $user->isAdmin()
            ? ($request->has('farm_id') ? $request->integer('farm_id') : null)
            : $user->farm_id;

        return $this->success($costingService->dashboard($farmId));
    }
}
