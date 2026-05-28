<?php

namespace Database\Factories;

use App\Models\ExamAttempt;
use App\Models\ExamRetake;
use App\Models\Student;
use App\Models\StudentEnrollment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ExamRetake>
 */
class ExamRetakeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'enrollment_id' => StudentEnrollment::factory(),
            'previous_attempt_id' => ExamAttempt::factory()->failed(),
            'new_attempt_id' => null,
            'reason' => 'Retake after failed attempt.',
            'planned_at' => now()->addWeek(),
            'status' => 'planned',
        ];
    }

    public function linked(ExamAttempt $newAttempt): static
    {
        return $this->state(fn (): array => [
            'new_attempt_id' => $newAttempt->id,
            'status' => 'scheduled',
        ]);
    }
}
