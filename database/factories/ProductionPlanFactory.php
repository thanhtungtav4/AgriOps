<?php

namespace Database\Factories;

use App\Models\Crop;
use App\Models\CropVariety;
use App\Models\Farm;
use App\Models\ProductionPlan;
use App\Models\SupplyDemand;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductionPlan>
 */
class ProductionPlanFactory extends Factory
{
    protected $model = ProductionPlan::class;

    public function definition(): array
    {
        return [
            'supply_contract_id' => null,
            'supply_demand_id' => SupplyDemand::factory(),
            'crop_id' => Crop::factory(),
            'variety_id' => CropVariety::factory(),
            'farm_id' => Farm::factory(),
            'quantity' => fake()->randomFloat(2, 100, 10000),
            'unit' => 'kg',
            'target_delivery_date' => now()->addDays(fake()->numberBetween(30, 180)),
            'estimated_cost' => fake()->randomFloat(2, 1000000, 50000000),
            'estimated_revenue' => fake()->randomFloat(2, 2000000, 100000000),
            'estimated_margin' => fake()->randomFloat(2, 500000, 30000000),
            'margin_percent' => fake()->randomFloat(2, 10, 50),
            'pricing_snapshot' => null,
            'status' => 'draft',
            'notes' => null,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
        ]);
    }
}
