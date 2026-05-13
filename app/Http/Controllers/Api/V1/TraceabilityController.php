<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\PackingLot;
use App\Services\PublicTraceabilityPresenter;
use Illuminate\Http\JsonResponse;

class TraceabilityController extends Controller
{
    use ApiResponse;

    public function show(string $qrCode, PublicTraceabilityPresenter $presenter): JsonResponse
    {
        $packingLot = PackingLot::where('qr_code', $qrCode)->first();

        if (!$packingLot) {
            return $this->notFoundError('Traceability record not found.');
        }

        return $this->success($presenter->present($packingLot));
    }
}
