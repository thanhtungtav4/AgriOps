<?php

namespace Database\Factories;

use App\Models\Farm;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Farm>
 */
class FarmFactory extends Factory
{
    protected $model = Farm::class;

    public function definition(): array
    {
        return [
            'name' => fake()->company() . ' Farm',
            'code' => 'FARM-' . fake()->unique()->numberBetween(1000, 9999),
            'address' => fake()->address(),
            'climate_zone' => fake()->randomElement(['tropical', 'subtropical', 'temperate', 'highland']),
            'responsible_person' => fake()->name(),
            'total_area_m2' => fake()->randomFloat(2, 1000, 500000),
            'status' => 'active',
            'certification' => null,
            'image_url' => null,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }

    public function withCertification(): static
    {
        return $this->state(fn (array $attributes) => [
            'certification' => ['VietGAP', 'GlobalGAP'],
        ]);
    }
}
