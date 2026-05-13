<?php

namespace Database\Factories;

use App\Models\Plot;
use App\Models\Bed;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Bed>
 */
class BedFactory extends Factory
{
    protected $model = Bed::class;

    public function definition(): array
    {
        return [
            'plot_id' => Plot::factory(),
            'code' => 'BED-' . fake()->unique()->numberBetween(1000, 9999),
            'length_m' => fake()->randomFloat(2, 5, 50),
            'width_m' => fake()->randomFloat(2, 1, 5),
            'area_m2' => fake()->randomFloat(2, 10, 200),
            'expected_plants' => fake()->numberBetween(50, 500),
            'status' => 'available',
            'notes' => null,
        ];
    }
}
