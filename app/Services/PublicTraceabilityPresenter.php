<?php

namespace App\Services;

use App\Models\PackingLot;

class PublicTraceabilityPresenter
{
    public function __construct(
        private readonly TraceabilityGraphService $traceabilityGraphService
    ) {}

    public function present(PackingLot $packingLot): array
    {
        $graph = $this->traceabilityGraphService->forPackingLot($packingLot);
        $sources = collect($graph['sources']);

        return [
            'qr_code' => $packingLot->qr_code,
            'packing_lot_code' => $graph['packing']['code'],
            'packed_at' => $graph['packing']['packed_at'],
            'status' => $this->publicStatus($graph['packing']['status']),
            'unit' => $graph['packing']['unit'],
            'total_output_quantity' => $graph['packing']['total_output_quantity'],
            'crop_names' => $sources
                ->pluck('batch.crop_name')
                ->filter()
                ->unique()
                ->values()
                ->all(),
            'farms' => $sources
                ->map(fn (array $source) => [
                    'name' => $source['farm_name'],
                    'code' => $source['farm_code'],
                    'certification' => $source['farm_certification'] ?? null,
                ])
                ->unique(fn (array $farm) => $farm['code'])
                ->values()
                ->all(),
            'safety_status' => $sources->every(fn (array $source) => $source['isolation_safe'])
                ? 'safety_controls_compliant'
                : 'pending_isolation_clearance',
            'sources' => $sources
                ->map(fn (array $source) => [
                    'harvest_lot_code' => $source['harvest_lot_code'],
                    'farm_name' => $source['farm_name'],
                    'farm_code' => $source['farm_code'],
                    'crop_name' => $source['batch']['crop_name'] ?? null,
                    'planting_batch_code' => $source['batch']['code'] ?? null,
                    'planted_at' => $source['batch']['planted_at'] ?? null,
                    'harvest_date' => $source['harvest_date'],
                    'quantity' => $source['quantity'],
                    'unit' => $source['unit'],
                    'processing_steps_count' => count($source['processing']),
                    'safety_status' => $source['isolation_safe']
                        ? 'safety_controls_compliant'
                        : 'pending_isolation_clearance',
                ])
                ->values()
                ->all(),
        ];
    }

    private function publicStatus(string $status): string
    {
        return match ($status) {
            'packed', 'published' => 'traceable',
            'cancelled' => 'cancelled',
            default => 'pending_publication',
        };
    }
}
