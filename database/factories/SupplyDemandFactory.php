<?php

namespace Database\Factories;

use App\Models\Crop;
use App\Models\Farm;
use App\Models\SupplyDemand;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SupplyDemand>
 */
class SupplyDemandFactory extends Factory
{
    protected $model = SupplyDemand::class;

    public function definition(): array
    {
        return [
            'farm_id' => Farm::factory(),
            'supply_contract_id' => null,
            'crop_id' => Crop::factory(),
            'quantity' => fake()->randomFloat(2, 100, 10000),
            'unit' => 'kg',
            'frequency' => fake()->randomElement(['daily', 'weekly', 'biweekly', 'monthly']),
            'target_date' => now()->addDays(fake()->numberBetween(30, 180)),
            'status' => 'pending',
            'notes' => null,
        ];
    }
}
