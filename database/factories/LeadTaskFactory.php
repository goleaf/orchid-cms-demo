<?php

namespace Database\Factories;

use App\Enums\LeadTaskPriority;
use App\Enums\LeadTaskStatus;
use App\Models\Lead;
use App\Models\LeadTask;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LeadTask>
 */
class LeadTaskFactory extends Factory
{
    protected $model = LeadTask::class;

    public function definition(): array
    {
        $title = $this->faker->sentence(5);

        return [
            'marketing_lead_id' => Lead::factory(),
            'assigned_to_user_id' => null,
            'created_by_user_id' => null,
            'title' => $title,
            'title_translations' => [
                'ru' => $title,
                'en' => $title,
            ],
            'description_translations' => null,
            'status' => LeadTaskStatus::Open,
            'priority' => LeadTaskPriority::Normal,
            'due_at' => now()->addHours($this->faker->numberBetween(1, 48)),
            'completed_at' => null,
            'cancelled_at' => null,
            'notes' => $this->faker->optional()->sentence(),
        ];
    }

    public function open(): static
    {
        return $this->state(fn (): array => [
            'status' => LeadTaskStatus::Open,
            'completed_at' => null,
            'cancelled_at' => null,
        ]);
    }

    public function inProgress(): static
    {
        return $this->state(fn (): array => [
            'status' => LeadTaskStatus::InProgress,
            'completed_at' => null,
            'cancelled_at' => null,
        ]);
    }

    public function done(): static
    {
        return $this->state(fn (): array => [
            'status' => LeadTaskStatus::Done,
            'completed_at' => now(),
            'cancelled_at' => null,
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (): array => [
            'status' => LeadTaskStatus::Cancelled,
            'cancelled_at' => now(),
            'completed_at' => null,
        ]);
    }

    public function overdue(): static
    {
        return $this->open()->state(fn (): array => [
            'due_at' => now()->subHour(),
        ]);
    }

    public function dueToday(): static
    {
        return $this->open()->state(fn (): array => [
            'due_at' => now()->addHours(2),
        ]);
    }

    public function low(): static
    {
        return $this->state(fn (): array => ['priority' => LeadTaskPriority::Low]);
    }

    public function normal(): static
    {
        return $this->state(fn (): array => ['priority' => LeadTaskPriority::Normal]);
    }

    public function high(): static
    {
        return $this->state(fn (): array => ['priority' => LeadTaskPriority::High]);
    }

    public function urgent(): static
    {
        return $this->state(fn (): array => ['priority' => LeadTaskPriority::Urgent]);
    }

    public function assigned(User|int|null $user = null): static
    {
        return $this->state(function () use ($user): array {
            $userModel = $user instanceof User ? $user : null;

            if ($userModel === null && $user === null) {
                $userModel = User::factory()->create();
            }

            return [
                'assigned_to_user_id' => $userModel?->getKey() ?? $user,
            ];
        });
    }

    public function unassigned(): static
    {
        return $this->state(fn (): array => ['assigned_to_user_id' => null]);
    }
}
