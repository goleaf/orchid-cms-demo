<?php

namespace Database\Factories;

use App\Models\ExamActivity;
use App\Models\ExamAdmission;
use App\Models\ExamAttempt;
use App\Models\ExamSession;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\TrainingGroup;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ExamActivity>
 */
class ExamActivityFactory extends Factory
{
    public function definition(): array
    {
        return [
            'exam_admission_id' => null,
            'exam_session_id' => null,
            'exam_attempt_id' => null,
            'enrollment_id' => StudentEnrollment::factory(),
            'student_profile_id' => Student::factory(),
            'training_group_id' => null,
            'user_id' => User::factory(),
            'type' => $this->faker->randomElement(['admission_saved', 'session_scheduled', 'attempt_recorded', 'retake_scheduled']),
            'title' => $this->faker->sentence(3),
            'body' => null,
            'old_value' => null,
            'new_value' => null,
            'meta' => null,
        ];
    }

    public function forAdmission(ExamAdmission $admission): static
    {
        return $this->state(fn (): array => [
            'exam_admission_id' => $admission->id,
            'enrollment_id' => $admission->enrollment_id,
            'student_profile_id' => $admission->student_profile_id,
            'training_group_id' => $admission->training_group_id,
        ]);
    }

    public function forSession(ExamSession $session): static
    {
        return $this->state(fn (): array => [
            'exam_session_id' => $session->id,
            'training_group_id' => $session->training_group_id,
        ]);
    }

    public function forAttempt(ExamAttempt $attempt): static
    {
        return $this->state(fn (): array => [
            'exam_attempt_id' => $attempt->id,
            'exam_admission_id' => $attempt->exam_admission_id,
            'exam_session_id' => $attempt->exam_session_id,
            'enrollment_id' => $attempt->enrollment_id,
            'student_profile_id' => $attempt->student_profile_id,
            'training_group_id' => $attempt->training_group_id,
        ]);
    }
}
