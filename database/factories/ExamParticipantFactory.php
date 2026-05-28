<?php

namespace Database\Factories;

use App\Enums\ExamParticipantStatus;
use App\Models\ExamParticipant;
use App\Models\ExamSession;
use App\Models\Student;
use App\Models\StudentEnrollment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ExamParticipant>
 */
class ExamParticipantFactory extends Factory
{
    public function definition(): array
    {
        return [
            'exam_session_id' => ExamSession::factory(),
            'student_id' => Student::factory(),
            'enrollment_id' => StudentEnrollment::factory(),
            'status' => ExamParticipantStatus::Registered->value,
            'admitted' => true,
            'block_reason' => null,
            'registered_at' => now(),
        ];
    }

    public function blocked(): static
    {
        return $this->state(fn (): array => [
            'status' => ExamParticipantStatus::Blocked->value,
            'admitted' => false,
            'block_reason' => 'Admission requirements are not complete.',
        ]);
    }
}
