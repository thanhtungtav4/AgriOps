<?php

namespace Database\Factories;

use App\Models\Farm;
use App\Models\WorkTask;
use App\Models\FarmingLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FarmingLog>
 */
class FarmingLogFactory extends Factory
{
    protected $model = FarmingLog::class;

    public function definition(): array
    {
        return [
            'farm_id' => Farm::factory(),
            'work_task_id' => WorkTask::factory(),
            'planting_batch_id' => null,
            'planting_batch_allocation_id' => null,
            'plot_id' => null,
            'bed_id' => null,
            'reported_by_user_id' => null,
            'logged_at' => now(),
            'status' => 'submitted',
            'actual_start_at' => now()->subHours(fake()->numberBetween(1, 4)),
            'actual_end_at' => now(),
            'notes' => fake()->sentence(),
            'photo_paths' => null,
            'metadata' => null,
        ];
    }

    public function withPhotos(): static
    {
        return $this->state(fn (array $attributes) => [
            'photo_paths' => [
                'logs/' . fake()->uuid() . '.jpg',
                'logs/' . fake()->uuid() . '.jpg',
            ],
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
        ]);
    }
}
