<?php

namespace App\Actions;

use App\Enums\EnrollmentStatus as EnrollmentStatusEnum;
use App\Models\EnrollmentStatus;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\TrainingGroup;
use App\Models\TrainingProgram;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateStudentEnrollmentAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(Student $student, array $data, ?User $user = null, bool $createOnboardingTasks = false): StudentEnrollment
    {
        return DB::transaction(function () use ($student, $data, $user, $createOnboardingTasks): StudentEnrollment {
            $course = $this->course($data['training_program_id'] ?? $data['course_id'] ?? null);
            $group = $this->group($data['training_group_id'] ?? null);
            $statusCode = $this->statusCode($data['status'] ?? null, $data['status_id'] ?? null);
            $trainingProgramId = $course?->id ?? $data['training_program_id'] ?? $data['course_id'] ?? $group?->training_program_id;

            $enrollment = StudentEnrollment::query()->create([
                'uuid' => (string) ($data['uuid'] ?? Str::uuid()),
                'enrollment_number' => $data['enrollment_number'] ?? app(GenerateEnrollmentNumberAction::class)->handle(),
                'student_profile_id' => $student->id,
                'lead_id' => $data['lead_id'] ?? $student->source_lead_id,
                'training_program_id' => $trainingProgramId,
                'course_category_id' => $data['course_category_id'] ?? $course?->course_category_id ?? $group?->course_category_id,
                'branch_id' => $data['branch_id'] ?? $group?->branch_id ?? $student->branch_id,
                'training_group_id' => null,
                'status' => $statusCode,
                'status_id' => $data['status_id'] ?? $this->statusId($statusCode),
                'manager_id' => $data['manager_id'] ?? $student->manager_id,
                'administrator_id' => $data['administrator_id'] ?? $student->administrator_id,
                'instructor_id' => $data['instructor_id'] ?? $group?->instructor_id,
                'teacher_id' => $data['teacher_id'] ?? null,
                'started_at' => $data['started_at'] ?? $data['start_date'] ?? null,
                'start_date' => $data['start_date'] ?? $data['started_at'] ?? null,
                'planned_end_date' => $data['planned_end_date'] ?? $group?->ends_on,
                'actual_end_date' => $data['actual_end_date'] ?? null,
                'completed_at' => $data['completed_at'] ?? null,
                'preferred_time' => $data['preferred_time'] ?? null,
                'training_language' => $data['training_language'] ?? $data['preferred_training_language'] ?? null,
                'format' => $data['format'] ?? $course?->format,
                'gearbox_type' => $data['gearbox_type'] ?? $course?->transmission,
                'contracted_price_cents' => $data['contracted_price_cents'] ?? $course?->price_cents ?? 0,
                'paid_cents' => $data['paid_cents'] ?? 0,
                'price' => $data['price'] ?? $course?->price,
                'discount' => $data['discount'] ?? 0,
                'currency' => $data['currency'] ?? $course?->currency ?? 'EUR',
                'payment_status' => $data['payment_status'] ?? 'pending',
                'theory_progress' => $data['theory_progress'] ?? 0,
                'practice_progress' => $data['practice_progress'] ?? 0,
                'total_theory_hours' => $data['total_theory_hours'] ?? $course?->theory_hours,
                'completed_theory_hours' => $data['completed_theory_hours'] ?? 0,
                'total_practice_hours' => $data['total_practice_hours'] ?? $course?->practice_hours,
                'completed_practice_hours' => $data['completed_practice_hours'] ?? 0,
                'notes' => $data['notes'] ?? null,
                'internal_notes' => $data['internal_notes'] ?? null,
                'created_by_id' => $data['created_by_id'] ?? $user?->id,
                'updated_by_id' => $data['updated_by_id'] ?? $user?->id,
            ]);

            app(RecordStudentActivityAction::class)->handle(
                $student,
                $user,
                'enrollment_created',
                tkey('students.activities.titles.enrollment_created'),
                null,
                null,
                null,
                ['enrollment_id' => $enrollment->id],
                $enrollment,
            );

            if ($group !== null) {
                $enrollment = app(AddStudentToTrainingGroupAction::class)->handle(
                    $enrollment->refresh(),
                    $group,
                    $user,
                    (bool) ($data['allow_overbooking'] ?? false),
                );
            }

            if ($createOnboardingTasks || (bool) ($data['create_onboarding_tasks'] ?? false)) {
                app(CreateStudentOnboardingTasksAction::class)->handle($student->refresh(), $user, $enrollment->refresh());
            }

            return $enrollment->refresh();
        });
    }

    private function course(mixed $courseId): ?TrainingProgram
    {
        return filled($courseId)
            ? TrainingProgram::query()->find($courseId)
            : null;
    }

    private function group(mixed $groupId): ?TrainingGroup
    {
        return filled($groupId)
            ? TrainingGroup::query()->find($groupId)
            : null;
    }

    private function statusCode(mixed $status, mixed $statusId): string
    {
        if (filled($status)) {
            return $status instanceof EnrollmentStatusEnum ? $status->value : (string) $status;
        }

        if (filled($statusId)) {
            $code = EnrollmentStatus::query()->whereKey($statusId)->value('code');

            if (filled($code)) {
                return (string) $code;
            }
        }

        return EnrollmentStatus::query()
            ->where('is_default', true)
            ->value('code') ?: EnrollmentStatusEnum::WaitingDocuments->value;
    }

    private function statusId(string $statusCode): ?int
    {
        return EnrollmentStatus::query()
            ->where('code', $statusCode)
            ->value('id');
    }
}
