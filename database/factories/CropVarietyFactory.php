<?php

namespace Database\Factories;

use App\Models\Crop;
use App\Models\CropVariety;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CropVariety>
 */
class CropVarietyFactory extends Factory
{
    protected $model = CropVariety::class;

    public function definition(): array
    {
        return [
            'crop_id' => Crop::factory(),
            'name' => fake()->randomElement(['F1 Hybrid', 'Local', 'Organic', 'Premium']) . ' ' . fake()->word(),
            'code' => 'VAR-' . fake()->unique()->numberBetween(1000, 9999),
            'supplier' => fake()->company(),
            'description' => fake()->sentence(),
            'avg_growth_days' => fake()->numberBetween(25, 90),
            'avg_yield_per_plant' => fake()->randomFloat(4, 0.1, 5.0),
            'planting_density_per_m2' => fake()->randomFloat(2, 5, 50),
            'disease_resistance' => fake()->randomElement(['low', 'medium', 'high']),
            'suitable_season' => fake()->randomElement(['spring', 'summer', 'autumn', 'winter', 'all_year']),
            'suitable_climate_zone' => fake()->randomElement(['tropical', 'subtropical', 'temperate', 'highland']),
            'care_requirements' => fake()->sentence(),
            'image_url' => null,
            'status' => 'active',
            'notes' => null,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }
}
