<?php

namespace App\Services;

use App\Models\ChemicalUsage;
use App\Models\PlantingBatch;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use InvalidArgumentException;

class IsolationGuardService
{
    public function activeIsolationUntil(PlantingBatch $batch, CarbonInterface|string $harvestDate): ?CarbonInterface
    {
        $harvestAt = is_string($harvestDate) ? Carbon::parse($harvestDate) : $harvestDate;

        $latestIsolation = ChemicalUsage::where('planting_batch_id', $batch->id)
            ->whereNotNull('isolation_ends_at')
            ->where('isolation_ends_at', '>', $harvestAt)
            ->orderByDesc('isolation_ends_at')
            ->first();

        return $latestIsolation?->isolation_ends_at;
    }

    public function assertCanHarvest(PlantingBatch $batch, CarbonInterface|string $harvestDate): void
    {
        $activeUntil = $this->activeIsolationUntil($batch, $harvestDate);

        if ($activeUntil) {
            throw new InvalidArgumentException(
                'Cannot harvest before chemical isolation period ends on ' . $activeUntil->toDateString() . '.'
            );
        }
    }
}
