<?php

namespace Database\Factories;

use App\Models\Farm;
use App\Models\PlantingBatch;
use App\Models\User;
use App\Models\CostRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CostRecord>
 */
class CostRecordFactory extends Factory
{
    protected $model = CostRecord::class;

    public function definition(): array
    {
        return [
            'farm_id' => Farm::factory(),
            'production_plan_id' => null,
            'planting_batch_id' => PlantingBatch::factory(),
            'recorded_by_user_id' => User::factory(),
            'cost_category' => fake()->randomElement(CostRecord::CATEGORIES),
            'amount' => fake()->randomFloat(2, 50000, 5000000),
            'quantity' => fake()->randomFloat(3, 1, 100),
            'unit' => fake()->randomElement(['kg', 'liter', 'unit', 'hour', 'day']),
            'occurred_at' => now()->subDays(fake()->numberBetween(0, 60)),
            'source_type' => null,
            'source_id' => null,
            'notes' => null,
            'metadata' => null,
        ];
    }
}
