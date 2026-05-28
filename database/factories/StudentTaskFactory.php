<?php

namespace Database\Factories;

use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\StudentTask;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudentTask>
 */
class StudentTaskFactory extends Factory
{
    protected $model = StudentTask::class;

    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'enrollment_id' => null,
            'title_translations' => [
                'ru' => 'Проверить данные ученика',
                'en' => 'Verify student data',
                'lt' => 'Patikrinti mokinio duomenis',
                'pl' => 'Sprawdzić dane ucznia',
            ],
            'description_translations' => null,
            'assigned_to_id' => User::factory(),
            'created_by_id' => User::factory(),
            'priority' => 'normal',
            'status' => 'open',
            'due_at' => now()->addDay(),
            'completed_at' => null,
            'cancelled_at' => null,
        ];
    }

    public function open(): static
    {
        return $this->state(fn (): array => [
            'status' => 'open',
            'completed_at' => null,
            'cancelled_at' => null,
        ]);
    }

    public function inProgress(): static
    {
        return $this->state(fn (): array => ['status' => 'in_progress']);
    }

    public function done(): static
    {
        return $this->state(fn (): array => [
            'status' => 'done',
            'completed_at' => now(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (): array => [
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);
    }

    public function overdue(): static
    {
        return $this->state(fn (): array => [
            'status' => 'open',
            'due_at' => now()->subHour(),
        ]);
    }

    public function dueToday(): static
    {
        return $this->state(fn (): array => [
            'status' => 'open',
            'due_at' => now()->addHours(2),
        ]);
    }

    public function assigned(): static
    {
        return $this->state(fn (): array => [
            'assigned_to_id' => User::factory(),
        ]);
    }

    public function unassigned(): static
    {
        return $this->state(fn (): array => ['assigned_to_id' => null]);
    }

    public function urgent(): static
    {
        return $this->state(fn (): array => ['priority' => 'urgent']);
    }

    public function high(): static
    {
        return $this->state(fn (): array => ['priority' => 'high']);
    }

    public function forEnrollment(): static
    {
        return $this->state(fn (): array => [
            'enrollment_id' => StudentEnrollment::factory(),
        ]);
    }
}
