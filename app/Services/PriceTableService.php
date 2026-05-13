<?php

namespace App\Services;

use App\Models\PriceTable;

class PriceTableService
{
    public function findActivePrice(
        int $cropId,
        ?int $varietyId,
        string $unit,
        string $date,
        ?int $farmId = null
    ): ?PriceTable {
        $query = PriceTable::query()
            ->activeForDate($date)
            ->where('crop_id', $cropId)
            ->where('unit', $unit)
            ->where(function ($query) use ($varietyId) {
                if ($varietyId) {
                    $query->where('variety_id', $varietyId)
                        ->orWhereNull('variety_id');
                } else {
                    $query->whereNull('variety_id');
                }
            })
            ->where(function ($query) use ($farmId) {
                if ($farmId) {
                    $query->where('farm_id', $farmId)
                        ->orWhereNull('farm_id');
                } else {
                    $query->whereNull('farm_id');
                }
            });

        return $query
            ->orderByRaw('CASE WHEN farm_id IS NULL THEN 1 ELSE 0 END')
            ->orderByRaw('CASE WHEN variety_id IS NULL THEN 1 ELSE 0 END')
            ->orderByDesc('effective_from')
            ->first();
    }

    public function snapshot(PriceTable $price): array
    {
        return [
            'price_table_id' => $price->id,
            'unit' => $price->unit,
            'grade_a_price' => (float) $price->grade_a_price,
            'grade_b_price' => (float) $price->grade_b_price,
            'grade_c_price' => (float) $price->grade_c_price,
            'side_channel_price' => (float) $price->side_channel_price,
            'effective_from' => $price->effective_from?->toDateString(),
            'effective_until' => $price->effective_until?->toDateString(),
            'captured_at' => now()->toIso8601String(),
        ];
    }
}
