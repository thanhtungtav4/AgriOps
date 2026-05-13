<?php

namespace Database\Factories;

use App\Models\Farm;
use App\Models\User;
use App\Models\Alert;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Alert>
 */
class AlertFactory extends Factory
{
    protected $model = Alert::class;

    public function definition(): array
    {
        return [
            'farm_id' => Farm::factory(),
            'recipient_user_id' => User::factory(),
            'recipient_role' => fake()->randomElement(['farm_manager', 'technician', 'worker', 'admin']),
            'alert_type' => fake()->randomElement(['overdue_task', 'yield_shortfall', 'isolation_warning', 'weather_alert', 'system']),
            'severity' => fake()->randomElement(Alert::SEVERITIES),
            'title' => fake()->sentence(4),
            'message' => fake()->sentence(),
            'source_type' => null,
            'source_id' => null,
            'context' => null,
            'notification_payload' => null,
            'status' => 'unread',
            'read_at' => null,
        ];
    }

    public function read(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'read',
            'read_at' => now(),
        ]);
    }

    public function critical(): static
    {
        return $this->state(fn (array $attributes) => [
            'severity' => 'critical',
        ]);
    }
}
