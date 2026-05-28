<?php

namespace Database\Factories;

use App\Enums\ExamAttemptStatus;
use App\Enums\ExamType;
use App\Models\DrivingLesson;
use App\Models\ExamAdmission;
use App\Models\ExamAttempt;
use App\Models\ExamSession;
use App\Models\Instructor;
use App\Models\Payment;
use App\Models\Student;
use App\Models\StudentDocument;
use App\Models\StudentEnrollment;
use App\Models\TrainingGroup;
use App\Models\TrainingProgram;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ExamAttempt>
 */
class ExamAttemptFactory extends Factory
{
    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'exam_admission_id' => ExamAdmission::factory(),
            'exam_session_id' => ExamSession::factory(),
            'enrollment_id' => StudentEnrollment::factory(),
            'student_id' => null,
            'student_profile_id' => Student::factory(),
            'training_group_id' => null,
            'training_program_id' => TrainingProgram::factory(),
            'instructor_id' => Instructor::factory(),
            'driving_lesson_id' => null,
            'student_document_id' => null,
            'payment_id' => null,
            'retake_of_attempt_id' => null,
            'exam_type' => ExamType::InternalTheory,
            'provider' => 'internal',
            'status' => ExamAttemptStatus::Scheduled,
            'status_id' => null,
            'attempt_number' => 1,
            'attempt_no' => 1,
            'score' => null,
            'max_score' => null,
            'passed' => false,
            'no_show' => false,
            'result_payload' => null,
            'started_at' => null,
            'finished_at' => null,
            'next_eligible_at' => null,
            'official_reference' => null,
            'official_payload' => null,
            'notes' => null,
            'internal_notes' => null,
            'created_by_id' => User::factory(),
            'updated_by_id' => null,
        ];
    }

    public function passed(): static
    {
        return $this->state(fn (): array => [
            'status' => ExamAttemptStatus::Passed,
            'score' => 90,
            'max_score' => 100,
            'passed' => true,
            'finished_at' => now(),
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (): array => [
            'status' => ExamAttemptStatus::Failed,
            'score' => 45,
            'max_score' => 100,
            'passed' => false,
            'finished_at' => now(),
            'next_eligible_at' => now()->addDays(7),
        ]);
    }

    public function forAdmission(ExamAdmission $admission): static
    {
        return $this->state(fn (): array => [
            'exam_admission_id' => $admission->id,
            'enrollment_id' => $admission->enrollment_id,
            'student_id' => $admission->student_profile_id,
            'student_profile_id' => $admission->student_profile_id,
            'training_group_id' => $admission->training_group_id,
            'training_program_id' => $admission->training_program_id,
            'instructor_id' => $admission->instructor_id,
            'exam_type' => $admission->admission_type,
            'provider' => $admission->admission_type->provider(),
        ]);
    }

    public function forSession(ExamSession $session): static
    {
        return $this->state(fn (): array => [
            'exam_session_id' => $session->id,
            'training_group_id' => $session->training_group_id,
            'training_program_id' => $session->training_program_id,
            'instructor_id' => $session->instructor_id,
            'exam_type' => $session->exam_type,
            'provider' => $session->provider,
        ]);
    }

    public function forDrivingLesson(DrivingLesson $lesson): static
    {
        return $this->state(fn (): array => [
            'driving_lesson_id' => $lesson->id,
        ]);
    }

    public function forDocument(StudentDocument $document): static
    {
        return $this->state(fn (): array => [
            'student_document_id' => $document->id,
        ]);
    }

    public function forPayment(Payment $payment): static
    {
        return $this->state(fn (): array => [
            'payment_id' => $payment->id,
        ]);
    }

    public function forGroup(TrainingGroup $group): static
    {
        return $this->state(fn (): array => [
            'training_group_id' => $group->id,
        ]);
    }
}
