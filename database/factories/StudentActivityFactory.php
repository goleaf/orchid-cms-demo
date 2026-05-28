<?php

namespace Database\Factories;

use App\Models\Lead;
use App\Models\Student;
use App\Models\StudentActivity;
use App\Models\StudentEnrollment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudentActivity>
 */
class StudentActivityFactory extends Factory
{
    protected $model = StudentActivity::class;

    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'enrollment_id' => null,
            'lead_id' => null,
            'user_id' => User::factory(),
            'type' => 'created',
            'title' => null,
            'body' => $this->faker->optional()->sentence(),
            'old_value' => null,
            'new_value' => null,
            'meta' => null,
        ];
    }

    public function created(): static
    {
        return $this->state(fn (): array => ['type' => 'created']);
    }

    public function createdFromLead(): static
    {
        return $this->state(fn (): array => [
            'type' => 'created_from_lead',
            'lead_id' => Lead::factory(),
        ]);
    }

    public function enrollmentCreated(): static
    {
        return $this->state(fn (): array => [
            'type' => 'enrollment_created',
            'enrollment_id' => StudentEnrollment::factory(),
        ]);
    }

    public function noteAdded(): static
    {
        return $this->state(fn (): array => [
            'type' => 'note_added',
            'body' => $this->faker->sentence(),
        ]);
    }

    public function statusChanged(): static
    {
        return $this->state(fn (): array => [
            'type' => 'status_changed',
            'old_value' => 'inactive',
            'new_value' => 'active',
        ]);
    }
}
