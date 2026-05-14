<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Services\ProfitReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfitController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly ProfitReportService $profitService
    ) {}

    /**
     * Estimate revenue for a batch (Section 25B.2)
     */
    public function estimateForBatch(int $batchId): JsonResponse
    {
        $result = $this->profitService->estimateRevenueForBatch($batchId);

        return $this->success($result);
    }

    /**
     * Calculate actual revenue for a batch (Section 25B.3)
     */
    public function actualForBatch(int $batchId): JsonResponse
    {
        $result = $this->profitService->calculateActualRevenueForBatch($batchId);

        return $this->success($result);
    }

    /**
     * Compare estimated vs actual (Section 25B.4)
     */
    public function compareForBatch(int $batchId): JsonResponse
    {
        $result = $this->profitService->compareRevenueForBatch($batchId);

        return $this->success($result);
    }

    /**
     * Profit summary by crop, month, top/bottom performers (Section 25B.4)
     */
    public function summary(Request $request): JsonResponse
    {
        $user = $request->user();
        $farmId = $user->isAdmin()
            ? ($request->input('farm_id') ?? $user->farm_id)
            : $user->farm_id;

        $from = $request->filled('from_date')
            ? \Carbon\Carbon::parse($request->input('from_date'))
            : null;
        $to = $request->filled('to_date')
            ? \Carbon\Carbon::parse($request->input('to_date'))
            : null;

        $result = $this->profitService->profitSummary($farmId, $from, $to);

        return $this->success($result);
    }

    /**
     * Efficiency metrics: revenue/m2, cost/m2
     */
    public function efficiency(Request $request): JsonResponse
    {
        $user = $request->user();
        $farmId = $user->isAdmin()
            ? ($request->input('farm_id') ?? $user->farm_id)
            : $user->farm_id;

        $result = $this->profitService->efficiencyMetrics($farmId);

        return $this->success($result);
    }
}