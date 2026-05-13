<?php

namespace Database\Factories;

use App\Models\Farm;
use App\Models\HarvestLot;
use App\Models\User;
use App\Models\ProcessingRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProcessingRecord>
 */
class ProcessingRecordFactory extends Factory
{
    protected $model = ProcessingRecord::class;

    public function definition(): array
    {
        $inputQty = fake()->randomFloat(3, 50, 500);
        $lossRate = fake()->randomFloat(4, 0.02, 0.15);
        $outputQty = $inputQty * (1 - $lossRate);

        return [
            'farm_id' => Farm::factory(),
            'harvest_lot_id' => HarvestLot::factory(),
            'processed_by_user_id' => User::factory(),
            'processed_at' => now(),
            'input_quantity' => $inputQty,
            'output_quantity' => $outputQty,
            'loss_quantity' => $inputQty - $outputQty,
            'loss_rate' => $lossRate,
            'unit' => 'kg',
            'status' => 'completed',
            'notes' => null,
            'metadata' => null,
        ];
    }
}
