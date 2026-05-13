<?php

namespace Database\Factories;

use App\Models\PlantingBatch;
use App\Models\Plot;
use App\Models\PlantingBatchAllocation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PlantingBatchAllocation>
 */
class PlantingBatchAllocationFactory extends Factory
{
    protected $model = PlantingBatchAllocation::class;

    public function definition(): array
    {
        return [
            'planting_batch_id' => PlantingBatch::factory(),
            'plot_id' => Plot::factory(),
            'bed_id' => null,
            'allocated_area_m2' => fake()->randomFloat(2, 100, 5000),
            'notes' => null,
            'status' => 'allocated',
        ];
    }
}
