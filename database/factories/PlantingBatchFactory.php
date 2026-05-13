<?php

namespace Database\Factories;

use App\Models\Crop;
use App\Models\CropVariety;
use App\Models\Farm;
use App\Models\PlantingBatch;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PlantingBatch>
 */
class PlantingBatchFactory extends Factory
{
    protected $model = PlantingBatch::class;

    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('-30 days', '+30 days');
        $harvestDays = fake()->numberBetween(30, 120);
        $harvestDate = (clone $startDate)->modify("+{$harvestDays} days");

        return [
            'farm_id' => Farm::factory(),
            'crop_id' => Crop::factory(),
            'variety_id' => CropVariety::factory(),
            'production_plan_id' => null,
            'code' => 'BATCH-' . fake()->unique()->numberBetween(10000, 99999),
            'planned_quantity' => fake()->randomFloat(2, 100, 10000),
            'planned_unit' => 'kg',
            'planned_area_m2' => fake()->randomFloat(2, 500, 50000),
            'planned_start_date' => $startDate,
            'planned_harvest_date' => $harvestDate,
            'actual_quantity' => null,
            'actual_area_m2' => null,
            'actual_start_date' => null,
            'actual_harvest_date' => null,
            'status' => 'planned',
            'notes' => null,
            'metadata' => null,
        ];
    }

    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'in_progress',
            'actual_start_date' => now()->subDays(fake()->numberBetween(5, 30)),
        ]);
    }

    public function harvesting(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'harvesting',
            'actual_start_date' => now()->subDays(fake()->numberBetween(30, 90)),
            'actual_harvest_date' => now(),
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'actual_start_date' => now()->subDays(fake()->numberBetween(60, 120)),
            'actual_harvest_date' => now()->subDays(fake()->numberBetween(1, 30)),
            'actual_quantity' => fake()->randomFloat(2, 100, 10000),
        ]);
    }
}
