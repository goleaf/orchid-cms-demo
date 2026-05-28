<?php

namespace App\Actions;

use App\Models\MarketingLead;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ConvertLeadToStudentAction
{
    /**
     * @param  array<string, mixed>  $studentData
     * @param  array<string, mixed>  $enrollmentData
     * @return array{lead: MarketingLead, student: Student, enrollment: StudentEnrollment}
     */
    public function handle(
        MarketingLead $lead,
        ?int $existingStudentId = null,
        array $studentData = [],
        array $enrollmentData = [],
        bool $createOnboardingTasks = true,
        bool $createDocumentPlaceholders = true,
        bool $createPaymentPlaceholder = true,
        ?User $user = null,
    ): array {
        $validation = app(ValidateLeadForStudentConversionAction::class)->handle($lead, $user, $enrollmentData);

        if (! $validation['can_convert']) {
            throw ValidationException::withMessages([
                'lead' => tkey($validation['blocking_errors'][0] ?? 'students.conversion.validation.lead_cannot_convert'),
            ]);
        }

        $prepared = app(PrepareLeadConversionDataAction::class)->handle($lead);
        $studentData = array_replace($prepared['student'], array_filter($studentData, fn (mixed $value): bool => $value !== null));
        $enrollmentData = array_replace($prepared['enrollment'], array_filter($enrollmentData, fn (mixed $value): bool => $value !== null));

        return DB::transaction(function () use ($lead, $existingStudentId, $studentData, $enrollmentData, $createOnboardingTasks, $createDocumentPlaceholders, $createPaymentPlaceholder, $user): array {
            $student = $existingStudentId === null
                ? app(CreateStudentAction::class)->handle($studentData, $user, false)
                : Student::query()->findOrFail($existingStudentId);

            if ($existingStudentId !== null && $studentData !== []) {
                $student = app(UpdateStudentAction::class)->handle(
                    $student,
                    array_filter($studentData, fn (mixed $value): bool => $value !== null),
                    $user,
                    true,
                );
            }

            $enrollment = app(CreateStudentEnrollmentAction::class)->handle($student, $enrollmentData, $user, false);

            if ($createOnboardingTasks) {
                app(CreateStudentOnboardingTasksAction::class)->handle($student->refresh(), $user, $enrollment->refresh());
            }

            if ($createDocumentPlaceholders) {
                app(PrepareStudentDocumentsPlaceholderAction::class)->handle($student->refresh(), $user);
            }

            if ($createPaymentPlaceholder) {
                app(PrepareStudentPaymentPlaceholderAction::class)->handle($student->refresh(), $user, $enrollment->refresh());
            }

            app(RecordStudentActivityAction::class)->handle(
                $student->refresh(),
                $user,
                'created_from_lead',
                tkey('students.activities.titles.created_from_lead'),
                null,
                null,
                null,
                ['lead_id' => $lead->id, 'enrollment_id' => $enrollment->id],
                $enrollment->refresh(),
                $lead,
            );

            $lead = app(MarkLeadAsConvertedAction::class)->handle($lead, $student->refresh(), $enrollment->refresh(), $user);

            return [
                'lead' => $lead,
                'student' => $student->refresh(),
                'enrollment' => $enrollment->refresh(),
            ];
        });
    }
}
