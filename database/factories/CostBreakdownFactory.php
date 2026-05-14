<?php

namespace Database\Factories;

use App\Models\CostBreakdown;
use App\Models\Farm;
use App\Models\PlantingBatch;
use App\Models\ProductionPlan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CostBreakdown>
 */
class CostBreakdownFactory extends Factory
{
    protected $model = CostBreakdown::class;

    public function definition(): array
    {
        return [
            'farm_id' => Farm::factory(),
            'breakdown_type' => 'production_plan',
            'production_plan_id' => ProductionPlan::factory(),
            'planting_batch_id' => null,
            'period_start' => fake()->dateTimeBetween('-6 months', '-3 months'),
            'period_end' => fake()->dateTimeBetween('-2 months', 'now'),
            'period_label' => fake()->randomElement(['Q1 2026', 'Season 2025-2026', 'Monthly Report']),
            'total_seed_cost' => fake()->randomFloat(2, 1000, 20000),
            'total_fertilizer_cost' => fake()->randomFloat(2, 2000, 30000),
            'total_chemical_cost' => fake()->randomFloat(2, 1500, 25000),
            'total_water_cost' => fake()->randomFloat(2, 500, 5000),
            'total_labor_cost' => fake()->randomFloat(2, 5000, 50000),
            'total_machinery_cost' => fake()->randomFloat(2, 1000, 10000),
            'total_land_rent_cost' => fake()->randomFloat(2, 2000, 15000),
            'total_other_cost' => fake()->randomFloat(2, 500, 5000),
            'total_cost' => 0, // Will be calculated
            'total_yield_kg' => fake()->randomFloat(3, 1000, 50000),
            'cost_per_kg' => null,
            'total_revenue' => fake()->randomFloat(2, 50000, 200000),
            'gross_margin' => null,
            'gross_margin_percent' => null,
            'breakdown_by_subcategory' => null,
            'cost_trends' => null,
            'variances' => null,
            'calculated_by' => null,
        ];
    }

    public function forFarm(): static
    {
        return $this->state(fn(array $attributes) => [
            'breakdown_type' => 'farm',
            'production_plan_id' => null,
            'planting_batch_id' => null,
        ]);
    }

    public function forBatch(): static
    {
        return $this->state(fn(array $attributes) => [
            'breakdown_type' => 'planting_batch',
            'production_plan_id' => null,
            'planting_batch_id' => PlantingBatch::factory(),
        ]);
    }
}