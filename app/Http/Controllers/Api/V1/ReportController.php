<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Services\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly ReportService $reportService
    ) {}

    /**
     * Section 26.1: Production Report
     */
    public function production(Request $request): JsonResponse
    {
        $user = $request->user();
        $farmId = $user->isAdmin()
            ? ($request->input('farm_id') ?? $user->farm_id)
            : $user->farm_id;

        $filters = [
            'from_date' => $request->input('from_date'),
            'to_date' => $request->input('to_date'),
            'crop_id' => $request->input('crop_id'),
            'planting_batch_id' => $request->input('planting_batch_id'),
        ];

        $result = $this->reportService->productionReport($farmId, array_filter($filters));

        return $this->success($result);
    }

    /**
     * Section 26.2: Yield Report
     */
    public function yield(Request $request): JsonResponse
    {
        $user = $request->user();
        $farmId = $user->isAdmin()
            ? ($request->input('farm_id') ?? $user->farm_id)
            : $user->farm_id;

        $filters = [
            'crop_id' => $request->input('crop_id'),
            'from_date' => $request->input('from_date'),
            'to_date' => $request->input('to_date'),
        ];

        $result = $this->reportService->yieldReport($farmId, array_filter($filters));

        return $this->success($result);
    }

    /**
     * Section 26.3: Loss Report
     */
    public function loss(Request $request): JsonResponse
    {
        $user = $request->user();
        $farmId = $user->isAdmin()
            ? ($request->input('farm_id') ?? $user->farm_id)
            : $user->farm_id;

        $filters = [
            'from_date' => $request->input('from_date'),
            'to_date' => $request->input('to_date'),
        ];

        $result = $this->reportService->lossReport($farmId, array_filter($filters));

        return $this->success($result);
    }

    /**
     * Section 26.5: Quality & Return Report
     */
    public function quality(Request $request): JsonResponse
    {
        $user = $request->user();
        $farmId = $user->isAdmin()
            ? ($request->input('farm_id') ?? $user->farm_id)
            : $user->farm_id;

        $filters = [
            'from_date' => $request->input('from_date'),
            'to_date' => $request->input('to_date'),
        ];

        $result = $this->reportService->qualityReturnReport($farmId, array_filter($filters));

        return $this->success($result);
    }

    /**
     * Task completion report
     */
    public function taskCompletion(Request $request): JsonResponse
    {
        $user = $request->user();
        $farmId = $user->isAdmin()
            ? ($request->input('farm_id') ?? $user->farm_id)
            : $user->farm_id;

        $filters = [
            'from_date' => $request->input('from_date'),
            'to_date' => $request->input('to_date'),
            'planting_batch_id' => $request->input('planting_batch_id'),
        ];

        $result = $this->reportService->taskCompletionReport($farmId, array_filter($filters));

        return $this->success($result);
    }
}