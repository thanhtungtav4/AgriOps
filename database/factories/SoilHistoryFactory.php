<?php

namespace Database\Factories;

use App\Models\Plot;
use App\Models\SoilHistory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SoilHistory>
 */
class SoilHistoryFactory extends Factory
{
    protected $model = SoilHistory::class;

    public function definition(): array
    {
        return [
            'plot_id' => Plot::factory(),
            'bed_id' => null,
            'record_type' => fake()->randomElement(['test_result', 'amendment', 'reading']),
            'recorded_at' => fake()->dateTimeBetween('-6 months', 'now'),
            'ph' => fake()->randomFloat(2, 5.0, 8.5),
            'nitrogen' => fake()->randomFloat(2, 10, 200),
            'phosphorus' => fake()->randomFloat(2, 5, 100),
            'potassium' => fake()->randomFloat(2, 50, 400),
            'organic_matter' => fake()->randomFloat(2, 1, 10),
            'moisture' => fake()->randomFloat(2, 20, 80),
            'zinc' => fake()->randomFloat(2, 0.5, 5),
            'iron' => fake()->randomFloat(2, 1, 50),
            'manganese' => fake()->randomFloat(2, 1, 30),
            'copper' => fake()->randomFloat(2, 0.5, 5),
            'boron' => fake()->randomFloat(2, 0.1, 3),
            'amendment_applied' => null,
            'amendment_quantity' => null,
            'amendment_unit' => null,
            'source' => fake()->randomElement(['lab', 'manual']),
            'lab_name' => null,
            'notes' => fake()->optional()->sentence(),
            'recorded_by' => User::factory(),
        ];
    }

    public function amendment(): static
    {
        return $this->state(fn(array $attributes) => [
            'record_type' => 'amendment',
            'amendment_applied' => fake()->randomElement(['lime', 'compost', 'gypsum', 'sulfate']),
            'amendment_quantity' => fake()->randomFloat(2, 50, 500),
            'amendment_unit' => 'kg',
        ]);
    }

    public function reading(): static
    {
        return $this->state(fn(array $attributes) => [
            'record_type' => 'reading',
            'source' => 'manual',
        ]);
    }
}