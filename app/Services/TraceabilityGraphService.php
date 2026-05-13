<?php

namespace App\Services;

use App\Models\ChemicalUsage;
use App\Models\HarvestLot;
use App\Models\PackingLot;
use App\Models\PackingLotSource;
use App\Models\PlantingBatch;
use App\Models\ProcessingRecord;

class TraceabilityGraphService
{
    public function forPackingLot(PackingLot $packingLot): array
    {
        $packingLot->loadCount('sources');

        $graph = [
            'packing' => $this->buildPackingNode($packingLot),
            'sources' => [],
        ];

        $sources = $packingLot->sources()->with([
            'harvestLot.farm',
            'harvestLot.plantingBatch.crop',
            'harvestLot.plantingBatch.variety',
            'harvestLot.processingRecords',
        ])->get();

        $unsafeBatchIds = $this->resolveUnsafeBatchIds($sources);

        foreach ($sources as $source) {
            $graph['sources'][] = $this->buildSourceNode($source, $unsafeBatchIds);
        }

        return $graph;
    }

    protected function buildPackingNode(PackingLot $packingLot): array
    {
        return [
            'code' => $packingLot->code,
            'packed_at' => $packingLot->packed_at?->toDateString() ?? '',
            'status' => $packingLot->status,
            'total_input_quantity' => (float) $packingLot->total_input_quantity,
            'total_output_quantity' => (float) $packingLot->total_output_quantity,
            'unit' => $packingLot->unit,
            'source_count' => $packingLot->sources_count ?? 0,
        ];
    }

    protected function buildSourceNode(PackingLotSource $source, array $unsafeBatchIds): array
    {
        $harvestLot = $source->harvestLot;
        $farm = $harvestLot?->farm;
        $batch = $harvestLot?->plantingBatch;
        $crop = $batch?->crop;

        return [
            'harvest_lot_code' => $harvestLot?->code ?? '',
            'harvest_date' => $harvestLot?->harvest_date?->toDateString() ?? '',
            'farm_name' => $farm?->name ?? '',
            'farm_code' => $farm?->code ?? '',
            'farm_certification' => $farm?->certification,
            'quantity' => (float) $source->quantity,
            'unit' => $source->unit,
            'status' => $harvestLot?->status ?? '',
            'processing' => $this->buildProcessingNodes($harvestLot),
            'batch' => $batch ? [
                'code' => $batch->code,
                'crop_name' => $crop?->name,
                'variety_name' => $batch->variety?->name,
                'planted_at' => $batch->actual_start_date?->toDateString()
                    ?? $batch->planned_start_date?->toDateString(),
            ] : null,
            'isolation_safe' => $batch === null || ! isset($unsafeBatchIds[$batch->id]),
        ];
    }

    protected function buildProcessingNodes(?HarvestLot $harvestLot): array
    {
        if (!$harvestLot) {
            return [];
        }

        return $harvestLot->processingRecords->map(fn (ProcessingRecord $record) => [
            'processed_at' => $record->processed_at?->toDateString() ?? '',
            'input_quantity' => (float) $record->input_quantity,
            'output_quantity' => (float) $record->output_quantity,
            'loss_rate' => (float) $record->loss_rate,
            'unit' => $record->unit,
            'status' => $record->status,
        ])->all();
    }

    protected function resolveUnsafeBatchIds($sources): array
    {
        $batchIds = $sources
            ->map(fn (PackingLotSource $source) => $source->harvestLot?->plantingBatch?->id)
            ->filter()
            ->unique()
            ->values();

        if ($batchIds->isEmpty()) {
            return [];
        }

        return ChemicalUsage::query()
            ->whereIn('planting_batch_id', $batchIds)
            ->whereNotNull('isolation_ends_at')
            ->where('isolation_ends_at', '>', now())
            ->pluck('planting_batch_id')
            ->mapWithKeys(fn (int $batchId) => [$batchId => true])
            ->all();
    }
}
