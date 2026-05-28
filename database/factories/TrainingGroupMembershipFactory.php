<?php

namespace Database\Factories;

use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\TrainingGroup;
use App\Models\TrainingGroupMembership;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<TrainingGroupMembership>
 */
class TrainingGroupMembershipFactory extends Factory
{
    protected $model = TrainingGroupMembership::class;

    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'training_group_id' => TrainingGroup::factory(),
            'student_profile_id' => Student::factory(),
            'enrollment_id' => fn (array $attributes): int => StudentEnrollment::factory()->create([
                'student_profile_id' => $attributes['student_profile_id'],
            ])->id,
            'status' => 'active',
            'joined_at' => now(),
            'left_at' => null,
            'transfer_from_group_id' => null,
            'transfer_to_group_id' => null,
            'transfer_reason' => null,
            'left_reason' => null,
            'notes' => null,
            'created_by_id' => null,
            'updated_by_id' => null,
        ];
    }

    public function invited(): static
    {
        return $this->state(fn (): array => ['status' => 'invited', 'joined_at' => null, 'left_at' => null]);
    }

    public function pending(): static
    {
        return $this->state(fn (): array => ['status' => 'pending', 'joined_at' => null, 'left_at' => null]);
    }

    public function active(): static
    {
        return $this->state(fn (): array => ['status' => 'active', 'left_at' => null]);
    }

    public function left(): static
    {
        return $this->state(fn (): array => ['status' => 'left', 'left_at' => now(), 'left_reason' => 'cancelled']);
    }

    public function waitlisted(): static
    {
        return $this->state(fn (): array => ['status' => 'waitlisted']);
    }

    public function transferred(): static
    {
        return $this->state(fn (): array => ['status' => 'transferred', 'left_at' => now(), 'transfer_reason' => 'group_changed']);
    }

    public function completed(): static
    {
        return $this->state(fn (): array => ['status' => 'completed', 'left_at' => now()]);
    }

    public function removed(): static
    {
        return $this->state(fn (): array => ['status' => 'removed', 'left_at' => now(), 'left_reason' => 'removed']);
    }

    public function cancelled(): static
    {
        return $this->state(fn (): array => ['status' => 'cancelled', 'left_at' => now(), 'left_reason' => 'cancelled']);
    }

    public function withStudent(): static
    {
        return $this->state(fn (): array => ['student_profile_id' => Student::factory()]);
    }

    public function withEnrollment(): static
    {
        return $this->state(fn (): array => ['enrollment_id' => StudentEnrollment::factory()]);
    }

    public function withGroup(): static
    {
        return $this->state(fn (): array => ['training_group_id' => TrainingGroup::factory()]);
    }

    public function joinedToday(): static
    {
        return $this->state(fn (): array => ['joined_at' => now(), 'left_at' => null]);
    }

    public function leftToday(): static
    {
        return $this->state(fn (): array => ['left_at' => now()]);
    }
}
