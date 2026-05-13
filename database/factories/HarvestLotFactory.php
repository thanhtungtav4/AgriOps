<?php

namespace Database\Factories;

use App\Models\Farm;
use App\Models\PlantingBatch;
use App\Models\HarvestLot;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<HarvestLot>
 */
class HarvestLotFactory extends Factory
{
    protected $model = HarvestLot::class;

    public function definition(): array
    {
        $rawQty = fake()->randomFloat(3, 50, 1000);

        return [
            'farm_id' => Farm::factory(),
            'planting_batch_id' => PlantingBatch::factory(),
            'pre_harvest_inspection_id' => null,
            'work_task_id' => null,
            'plot_id' => null,
            'bed_id' => null,
            'harvested_by_user_id' => null,
            'code' => 'HARV-' . fake()->unique()->numberBetween(10000, 99999),
            'harvest_date' => now(),
            'raw_quantity' => $rawQty,
            'unit' => 'kg',
            'grade_a_quantity' => $rawQty * 0.7,
            'grade_b_quantity' => $rawQty * 0.15,
            'grade_c_quantity' => $rawQty * 0.1,
            'reject_quantity' => $rawQty * 0.05,
            'reject_reasons' => null,
            'status' => 'available',
            'notes' => null,
            'metadata' => null,
        ];
    }

    public function packed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'packed',
        ]);
    }

    public function reserved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'reserved',
        ]);
    }
}
