<?php

namespace Database\Factories;

use App\Enums\ExamAdmissionStatus;
use App\Enums\ExamChecklistItemStatus;
use App\Enums\ExamType;
use App\Models\Branch;
use App\Models\ExamAdmission;
use App\Models\Instructor;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\TrainingGroup;
use App\Models\TrainingProgram;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ExamAdmission>
 */
class ExamAdmissionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'enrollment_id' => StudentEnrollment::factory(),
            'student_profile_id' => Student::factory(),
            'training_group_id' => null,
            'training_program_id' => TrainingProgram::factory(),
            'branch_id' => Branch::factory(),
            'instructor_id' => Instructor::factory(),
            'admission_type' => ExamType::InternalTheory,
            'status' => ExamAdmissionStatus::Checking,
            'required_theory_hours' => 40,
            'completed_theory_hours' => 20,
            'required_practice_hours' => 30,
            'completed_practice_hours' => 10,
            'documents_status' => ExamChecklistItemStatus::Pending->value,
            'payment_status' => ExamChecklistItemStatus::Pending->value,
            'checklist_status' => ExamChecklistItemStatus::Pending->value,
            'admitted_at' => null,
            'rejected_at' => null,
            'expires_at' => now()->addMonths(3),
            'notes' => null,
            'internal_notes' => null,
            'meta' => null,
            'created_by_id' => User::factory(),
            'updated_by_id' => null,
        ];
    }

    public function internalPractical(): static
    {
        return $this->state(fn (): array => [
            'admission_type' => ExamType::InternalPractical,
        ]);
    }

    public function stateTheory(): static
    {
        return $this->state(fn (): array => [
            'admission_type' => ExamType::StateTheory,
        ]);
    }

    public function statePractical(): static
    {
        return $this->state(fn (): array => [
            'admission_type' => ExamType::StatePractical,
        ]);
    }

    public function ready(): static
    {
        return $this->state(fn (): array => [
            'status' => ExamAdmissionStatus::Ready,
            'checklist_status' => ExamChecklistItemStatus::Passed->value,
            'admitted_at' => now(),
            'documents_status' => ExamChecklistItemStatus::Passed->value,
            'payment_status' => ExamChecklistItemStatus::Passed->value,
            'completed_theory_hours' => 40,
            'completed_practice_hours' => 30,
        ]);
    }

    public function forEnrollment(StudentEnrollment $enrollment): static
    {
        return $this->state(fn (): array => [
            'enrollment_id' => $enrollment->id,
            'student_profile_id' => $enrollment->student_profile_id,
            'training_group_id' => $enrollment->training_group_id,
            'training_program_id' => $enrollment->training_program_id,
            'branch_id' => $enrollment->branch_id,
            'instructor_id' => $enrollment->instructor_id,
            'required_theory_hours' => $enrollment->total_theory_hours,
            'completed_theory_hours' => $enrollment->completed_theory_hours,
            'required_practice_hours' => $enrollment->total_practice_hours,
            'completed_practice_hours' => $enrollment->completed_practice_hours,
        ]);
    }

    public function forGroup(TrainingGroup $group): static
    {
        return $this->state(fn (): array => [
            'training_group_id' => $group->id,
            'training_program_id' => $group->training_program_id,
            'branch_id' => $group->branch_id,
            'instructor_id' => $group->instructor_id,
        ]);
    }
}
