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
            'enrollment_id' => StudentEnrollment::factory(),
            'status' => 'active',
            'joined_at' => now(),
            'left_at' => null,
            'left_reason' => null,
            'notes' => null,
            'created_by_id' => null,
            'updated_by_id' => null,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (): array => ['status' => 'active', 'left_at' => null]);
    }

    public function left(): static
    {
        return $this->state(fn (): array => ['status' => 'left', 'left_at' => now(), 'left_reason' => 'cancelled']);
    }
}
