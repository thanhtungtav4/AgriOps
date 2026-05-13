<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PublicTraceabilityPageController;
use App\Http\Controllers\OperationsController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/traceability/{qrCode}', [PublicTraceabilityPageController::class, 'show']);

Route::get('/operations', [OperationsController::class, 'index']);
Route::get('/operations/login', [OperationsController::class, 'index']);
Route::get('/operations/{any}', [OperationsController::class, 'index'])
    ->where('any', '.*');