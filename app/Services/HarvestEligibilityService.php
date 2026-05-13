<?php

namespace App\Services;

use App\Models\PlantingBatch;
use App\Models\PreHarvestInspection;
use Carbon\CarbonInterface;
use InvalidArgumentException;

class HarvestEligibilityService
{
    public function __construct(
        private readonly IsolationGuardService $isolationGuardService
    ) {}

    public function assertCanHarvest(PlantingBatch $batch, CarbonInterface|string $harvestDate): void
    {
        $inspection = PreHarvestInspection::where('planting_batch_id', $batch->id)
            ->orderByDesc('inspected_at')
            ->orderByDesc('id')
            ->first();

        if (!$inspection) {
            throw new InvalidArgumentException('Pre-harvest inspection is required before harvest.');
        }

        if ($inspection->status !== 'approved') {
            throw new InvalidArgumentException('Latest pre-harvest inspection is not approved.');
        }

        $this->isolationGuardService->assertCanHarvest($batch, $harvestDate);
    }
}
