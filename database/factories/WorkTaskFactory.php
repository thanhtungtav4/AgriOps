<?php

namespace Database\Factories;

use App\Models\Farm;
use App\Models\PlantingBatch;
use App\Models\PlantingBatchAllocation;
use App\Models\WorkTask;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WorkTask>
 */
class WorkTaskFactory extends Factory
{
    protected $model = WorkTask::class;

    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('-7 days', '+30 days');
        $dueDate = (clone $startDate)->modify('+' . fake()->numberBetween(1, 14) . ' days');

        return [
            'farm_id' => Farm::factory(),
            'planting_batch_id' => PlantingBatch::factory(),
            'planting_batch_allocation_id' => PlantingBatchAllocation::factory(),
            'plot_id' => null,
            'bed_id' => null,
            'growth_stage_id' => null,
            'assigned_user_id' => null,
            'title' => fake()->randomElement(['Tưới nước', 'Bón phân', 'Phun thuốc', 'Làm cỏ', 'Kiểm tra sâu bệnh', 'Thu hoạch']),
            'task_type' => fake()->randomElement(['maintenance', 'treatment', 'harvest', 'inspection']),
            'status' => 'planned',
            'priority' => 'normal',
            'planned_start_date' => $startDate,
            'planned_due_date' => $dueDate,
            'started_at' => null,
            'completed_at' => null,
            'instructions' => fake()->sentence(),
            'completion_note' => null,
            'metadata' => null,
        ];
    }

    public function assigned(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'assigned',
            'started_at' => now(),
        ]);
    }

    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'in_progress',
            'started_at' => now()->subHours(fake()->numberBetween(1, 8)),
        ]);
    }

    public function done(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'done',
            'started_at' => now()->subHours(fake()->numberBetween(4, 24)),
            'completed_at' => now(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
        ]);
    }

    public function requiresPhoto(): static
    {
        return $this->state(fn (array $attributes) => [
            'metadata' => ['requires_photo' => true],
        ]);
    }

    public function highPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'high',
        ]);
    }

    public function urgent(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'urgent',
        ]);
    }
}
