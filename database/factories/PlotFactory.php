<?php

namespace Database\Factories;

use App\Models\Farm;
use App\Models\Plot;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Plot>
 */
class PlotFactory extends Factory
{
    protected $model = Plot::class;

    public function definition(): array
    {
        return [
            'farm_id' => Farm::factory(),
            'code' => 'PLOT-' . fake()->unique()->numberBetween(1000, 9999),
            'name' => fake()->randomElement(['Khu A', 'Khu B', 'Khu C', 'Lô 1', 'Lô 2']) . ' ' . fake()->numberBetween(1, 20),
            'area_m2' => fake()->randomFloat(2, 100, 50000),
            'soil_type' => fake()->randomElement(['clay', 'sandy', 'loam', 'silt']),
            'water_source' => fake()->randomElement(['well', 'river', 'irrigation', 'rain']),
            'status' => 'available',
            'current_crop_id' => null,
            'current_batch_id' => null,
            'notes' => null,
        ];
    }

    public function occupied(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'occupied',
        ]);
    }

    public function resting(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'resting',
        ]);
    }

    public function suspended(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'suspended',
        ]);
    }
}
