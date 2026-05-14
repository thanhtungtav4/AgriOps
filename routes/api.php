<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\AlertController;
use App\Http\Controllers\Api\V1\ChemicalUsageController;
use App\Http\Controllers\Api\V1\CostRecordController;
use App\Http\Controllers\Api\V1\DeliveryController;
use App\Http\Controllers\Api\V1\FarmApiController;
use App\Http\Controllers\Api\V1\HarvestLotController;
use App\Http\Controllers\Api\V1\IncidentController;
use App\Http\Controllers\Api\V1\MarginDashboardController;
use App\Http\Controllers\Api\V1\PlotApiController;
use App\Http\Controllers\Api\V1\CropApiController;
use App\Http\Controllers\Api\V1\CropVarietyApiController;
use App\Http\Controllers\Api\V1\PlanningController;
use App\Http\Controllers\Api\V1\PlantingBatchAllocationController;
use App\Http\Controllers\Api\V1\PlantingBatchController;
use App\Http\Controllers\Api\V1\PreHarvestInspectionController;
use App\Http\Controllers\Api\V1\PriceTableController;
use App\Http\Controllers\Api\V1\ReturnRecordController;
use App\Http\Controllers\Api\V1\SupplyContractController;
use App\Http\Controllers\Api\V1\SupplyDemandController;
use App\Http\Controllers\Api\V1\TraceabilityController;
use App\Http\Controllers\Api\V1\WorkTaskLogController;
use App\Http\Controllers\Api\V1\PackingLotController;
use App\Http\Controllers\Api\V1\WorkTaskController;
use App\Http\Controllers\Api\V1\PostSeasonReviewController;
use App\Http\Controllers\Api\V1\SoilHistoryController;
use App\Http\Controllers\Api\V1\ChemicalProductController;
use App\Http\Controllers\Api\V1\CostBreakdownController;
use App\Http\Controllers\Api\V1\ProfitController;
use App\Http\Controllers\Api\V1\ReportController;
use App\Http\Controllers\Api\V1\CrossFarmAllocationController;
use App\Http\Controllers\Api\V1\IrrigationLogController;
use App\Http\Controllers\Api\V1\FertilizerLogController;
use App\Http\Controllers\Api\V1\ApprovalController;

Route::prefix('v1')->group(function () {
    Route::post('/auth/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1');
    Route::get('/traceability/{qrCode}', [TraceabilityController::class, 'show']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);

        Route::middleware('farm.scope')->group(function () {
            Route::get('/farms', [FarmApiController::class, 'index']);
            Route::get('/farms/{id}', [FarmApiController::class, 'show']);
            Route::get('/plots', [PlotApiController::class, 'index']);
            Route::get('/plots/{id}', [PlotApiController::class, 'show']);
            Route::get('/supply-contracts', [SupplyContractController::class, 'index']);
            Route::post('/supply-contracts', [SupplyContractController::class, 'store']);
            Route::get('/supply-contracts/{id}', [SupplyContractController::class, 'show']);
            Route::get('/supply-demands', [SupplyDemandController::class, 'index']);
            Route::post('/supply-demands', [SupplyDemandController::class, 'store']);
            Route::get('/supply-demands/{id}', [SupplyDemandController::class, 'show']);

            Route::get('/planting-batches', [PlantingBatchController::class, 'index']);
            Route::get('/planting-batches/{id}', [PlantingBatchController::class, 'show']);
            Route::post('/planting-batches', [PlantingBatchController::class, 'store']);
            Route::patch('/planting-batches/{id}/transition', [PlantingBatchController::class, 'transition']);
            Route::post('/planting-batches/{id}/allocations', [PlantingBatchAllocationController::class, 'store']);
            Route::delete('/planting-batches/{id}/allocations/{allocationId}', [PlantingBatchAllocationController::class, 'destroy']);

            Route::get('/work-tasks', [WorkTaskController::class, 'index']);
            Route::get('/work-tasks/{id}', [WorkTaskController::class, 'show']);
            Route::patch('/work-tasks/{id}/status', [WorkTaskController::class, 'updateStatus']);
            Route::post('/work-tasks/{id}/logs', [WorkTaskLogController::class, 'store']);
            Route::post('/planting-batches/{id}/generate-work-tasks', [WorkTaskController::class, 'generateWorkTasks']);
            Route::get('/incidents', [IncidentController::class, 'index']);
            Route::post('/incidents', [IncidentController::class, 'store']);
            Route::get('/incidents/{id}', [IncidentController::class, 'show']);
            Route::get('/chemical-usages', [ChemicalUsageController::class, 'index']);
            Route::post('/chemical-usages', [ChemicalUsageController::class, 'store']);
            Route::get('/chemical-usages/{id}', [ChemicalUsageController::class, 'show']);
            Route::get('/pre-harvest-inspections', [PreHarvestInspectionController::class, 'index']);
            Route::post('/pre-harvest-inspections', [PreHarvestInspectionController::class, 'store']);
            Route::get('/pre-harvest-inspections/{id}', [PreHarvestInspectionController::class, 'show']);
            Route::post('/pre-harvest-inspections/{id}/approve', [PreHarvestInspectionController::class, 'approve']);
            Route::post('/pre-harvest-inspections/{id}/reject', [PreHarvestInspectionController::class, 'reject']);
            Route::get('/harvest-lots', [HarvestLotController::class, 'index']);
            Route::post('/harvest-lots', [HarvestLotController::class, 'store']);
            Route::get('/harvest-lots/{id}', [HarvestLotController::class, 'show']);

            Route::get('/packing-lots', [PackingLotController::class, 'index']);
            Route::post('/packing-lots', [PackingLotController::class, 'store']);
            Route::get('/packing-lots/{id}', [PackingLotController::class, 'show']);
            Route::get('/deliveries', [DeliveryController::class, 'index']);
            Route::post('/deliveries', [DeliveryController::class, 'store']);
            Route::get('/deliveries/{id}', [DeliveryController::class, 'show']);
            Route::get('/deliveries/{id}/revenue', [DeliveryController::class, 'revenue']);
            Route::get('/returns', [ReturnRecordController::class, 'index']);
            Route::post('/returns', [ReturnRecordController::class, 'store']);
            Route::get('/returns/{id}', [ReturnRecordController::class, 'show']);
            Route::get('/price-tables', [PriceTableController::class, 'index']);
            Route::post('/price-tables', [PriceTableController::class, 'store']);
            Route::get('/cost-records', [CostRecordController::class, 'index']);
            Route::post('/cost-records', [CostRecordController::class, 'store']);
            Route::get('/margin-dashboard', MarginDashboardController::class);
            Route::get('/alerts', [AlertController::class, 'index']);
            Route::post('/alerts/trigger', [AlertController::class, 'trigger']);
            Route::patch('/alerts/{id}/read', [AlertController::class, 'markRead']);

            Route::post('/planning/calculate', [PlanningController::class, 'calculate']);
            Route::post('/production-plans', [PlanningController::class, 'store']);
            Route::get('/production-plans', [PlanningController::class, 'index']);

            // Post-season reviews
            Route::get('/post-season-reviews', [PostSeasonReviewController::class, 'index']);
            Route::post('/post-season-reviews', [PostSeasonReviewController::class, 'store']);
            Route::get('/post-season-reviews/{id}', [PostSeasonReviewController::class, 'show']);
            Route::patch('/post-season-reviews/{id}', [PostSeasonReviewController::class, 'update']);
            Route::post('/post-season-reviews/{id}/submit', [PostSeasonReviewController::class, 'submit']);
            Route::post('/post-season-reviews/{id}/approve', [PostSeasonReviewController::class, 'approve']);
            Route::post('/post-season-reviews/{id}/reject', [PostSeasonReviewController::class, 'reject']);
            Route::delete('/post-season-reviews/{id}', [PostSeasonReviewController::class, 'destroy']);

            // Soil history
            Route::get('/soil-histories', [SoilHistoryController::class, 'index']);
            Route::post('/soil-histories', [SoilHistoryController::class, 'store']);
            Route::get('/soil-histories/{id}', [SoilHistoryController::class, 'show']);
            Route::patch('/soil-histories/{id}', [SoilHistoryController::class, 'update']);
            Route::delete('/soil-histories/{id}', [SoilHistoryController::class, 'destroy']);

            // Chemical products
            Route::get('/chemical-products', [ChemicalProductController::class, 'index']);
            Route::post('/chemical-products', [ChemicalProductController::class, 'store']);
            Route::get('/chemical-products/low-stock', [ChemicalProductController::class, 'lowStock']);
            Route::get('/chemical-products/{id}', [ChemicalProductController::class, 'show']);
            Route::patch('/chemical-products/{id}', [ChemicalProductController::class, 'update']);
            Route::post('/chemical-products/{id}/stock', [ChemicalProductController::class, 'updateStock']);
            Route::delete('/chemical-products/{id}', [ChemicalProductController::class, 'destroy']);

            // Cost breakdowns
            Route::get('/cost-breakdowns', [CostBreakdownController::class, 'index']);
            Route::post('/cost-breakdowns/plan/{planId}', [CostBreakdownController::class, 'calculateForPlan']);
            Route::post('/cost-breakdowns/batch/{batchId}', [CostBreakdownController::class, 'calculateForBatch']);
            Route::post('/cost-breakdowns/farm/{farmId}', [CostBreakdownController::class, 'calculateForFarm']);
            Route::get('/cost-breakdowns/dashboard', [CostBreakdownController::class, 'dashboardSummary']);
            Route::post('/cost-breakdowns/compare', [CostBreakdownController::class, 'compare']);
            Route::get('/cost-breakdowns/{id}', [CostBreakdownController::class, 'show']);
            Route::delete('/cost-breakdowns/{id}', [CostBreakdownController::class, 'destroy']);

            // Profit & Revenue (Section 25B)
            Route::get('/profit/batch/{batchId}/estimate', [ProfitController::class, 'estimateForBatch']);
            Route::get('/profit/batch/{batchId}/actual', [ProfitController::class, 'actualForBatch']);
            Route::get('/profit/batch/{batchId}/compare', [ProfitController::class, 'compareForBatch']);
            Route::get('/profit/summary', [ProfitController::class, 'summary']);
            Route::get('/profit/efficiency', [ProfitController::class, 'efficiency']);

            // Reports (Section 26)
            Route::get('/reports/production', [ReportController::class, 'production']);
            Route::get('/reports/yield', [ReportController::class, 'yield']);
            Route::get('/reports/loss', [ReportController::class, 'loss']);
            Route::get('/reports/quality', [ReportController::class, 'quality']);
            Route::get('/reports/tasks', [ReportController::class, 'taskCompletion']);

            // Cross-farm allocation (Section 22)
            Route::get('/cross-farm-allocations', [CrossFarmAllocationController::class, 'index']);
            Route::post('/cross-farm-allocations', [CrossFarmAllocationController::class, 'store']);
            Route::post('/cross-farm-allocations/{id}/approve', [CrossFarmAllocationController::class, 'approve']);
            Route::post('/cross-farm-allocations/{id}/reject', [CrossFarmAllocationController::class, 'reject']);
            Route::post('/cross-farm-allocations/{id}/fulfill', [CrossFarmAllocationController::class, 'fulfill']);
            Route::get('/cross-farm-allocations/available-farms', [CrossFarmAllocationController::class, 'availableFarms']);

            // Irrigation & Fertilizer logs (Section 8, 14.2)
            Route::get('/irrigation-logs', [IrrigationLogController::class, 'index']);
            Route::post('/irrigation-logs', [IrrigationLogController::class, 'store']);
            Route::get('/irrigation-logs/{id}', [IrrigationLogController::class, 'show']);
            Route::patch('/irrigation-logs/{id}', [IrrigationLogController::class, 'update']);
            Route::delete('/irrigation-logs/{id}', [IrrigationLogController::class, 'destroy']);

            Route::get('/fertilizer-logs', [FertilizerLogController::class, 'index']);
            Route::post('/fertilizer-logs', [FertilizerLogController::class, 'store']);
            Route::get('/fertilizer-logs/{id}', [FertilizerLogController::class, 'show']);
            Route::patch('/fertilizer-logs/{id}', [FertilizerLogController::class, 'update']);
            Route::delete('/fertilizer-logs/{id}', [FertilizerLogController::class, 'destroy']);

            // Centralized Approvals (Section 23)
            Route::get('/approvals', [ApprovalController::class, 'index']);
            Route::get('/approvals/pending', [ApprovalController::class, 'pending']);
            Route::post('/approvals/{id}/approve', [ApprovalController::class, 'approve']);
            Route::post('/approvals/{id}/reject', [ApprovalController::class, 'reject']);
            Route::post('/approvals/{id}/remind', [ApprovalController::class, 'sendReminder']);
        });

        Route::get('/crops', [CropApiController::class, 'index']);
        Route::get('/crops/{id}', [CropApiController::class, 'show']);
        Route::get('/crop-varieties', [CropVarietyApiController::class, 'index']);
        Route::get('/crop-varieties/{id}', [CropVarietyApiController::class, 'show']);
    });
});
