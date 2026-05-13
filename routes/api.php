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
        });

        Route::get('/crops', [CropApiController::class, 'index']);
        Route::get('/crops/{id}', [CropApiController::class, 'show']);
        Route::get('/crop-varieties', [CropVarietyApiController::class, 'index']);
        Route::get('/crop-varieties/{id}', [CropVarietyApiController::class, 'show']);
    });
});
