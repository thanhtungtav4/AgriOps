<?php

namespace App\Http\Controllers;

use App\Models\PackingLot;
use App\Services\PublicTraceabilityPresenter;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;

class PublicTraceabilityPageController extends Controller
{
    public function show(string $qrCode, PublicTraceabilityPresenter $presenter): View|Response
    {
        $packingLot = PackingLot::where('qr_code', $qrCode)->first();

        if (!$packingLot) {
            abort(404);
        }

        return view('traceability.show', [
            'trace' => $presenter->present($packingLot),
        ]);
    }
}
