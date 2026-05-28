<?php

namespace App\Actions;

use App\Enums\EnrollmentStatus as EnrollmentStatusEnum;
use App\Models\EnrollmentStatus;
use App\Models\StudentEnrollment;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class ChangeEnrollmentStatusAction
{
    public function handle(StudentEnrollment $enrollment, EnrollmentStatusEnum|string $status, ?User $user = null, bool $allowOverride = false): StudentEnrollment
    {
        $targetStatus = $status instanceof EnrollmentStatusEnum ? $status : EnrollmentStatusEnum::from((string) $status);
        $override = $allowOverride || ($user?->hasAccess('students.enrollments.override_status_transition') ?? false);

        if (! $override && ! $this->transitionAllowed($enrollment->status, $targetStatus)) {
            throw ValidationException::withMessages([
                'status' => tkey('students.validation.invalid_enrollment_status_transition'),
            ]);
        }

        $oldStatus = $enrollment->status->value;
        $ending = in_array($targetStatus, [
            EnrollmentStatusEnum::Completed,
            EnrollmentStatusEnum::Cancelled,
            EnrollmentStatusEnum::Expelled,
        ], true);

        $enrollment->forceFill([
            'status' => $targetStatus->value,
            'status_id' => EnrollmentStatus::query()->where('code', $targetStatus->value)->value('id') ?: $enrollment->status_id,
            'actual_end_date' => $ending ? ($enrollment->actual_end_date ?? now()->toDateString()) : $enrollment->actual_end_date,
            'completed_at' => $targetStatus === EnrollmentStatusEnum::Completed ? ($enrollment->completed_at ?? now()->toDateString()) : $enrollment->completed_at,
            'updated_by_id' => $user?->id ?? $enrollment->updated_by_id,
        ])->save();

        app(RecordStudentActivityAction::class)->handle(
            $enrollment->student,
            $user,
            'enrollment_status_changed',
            tkey('students.activities.titles.enrollment_status_changed'),
            null,
            $oldStatus,
            $targetStatus->value,
            ['enrollment_id' => $enrollment->id],
            $enrollment->refresh(),
        );

        return $enrollment->refresh();
    }

    public function transitionAllowed(EnrollmentStatusEnum $from, EnrollmentStatusEnum $to): bool
    {
        if ($from === $to) {
            return true;
        }

        return in_array($to->value, $this->allowedTransitions()[$from->value] ?? [], true);
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function allowedTransitions(): array
    {
        return [
            'draft' => ['waiting_documents', 'waiting_payment', 'waiting_start', 'cancelled'],
            'pending' => ['waiting_documents', 'waiting_payment', 'waiting_start', 'active', 'cancelled'],
            'waiting_documents' => ['waiting_payment', 'waiting_start', 'active', 'cancelled'],
            'waiting_payment' => ['waiting_start', 'active', 'cancelled'],
            'waiting_start' => ['active', 'cancelled'],
            'active' => ['theory', 'practice', 'paused', 'completed', 'cancelled'],
            'theory' => ['practice', 'paused', 'cancelled'],
            'practice' => ['ready_internal_exam', 'ready_state_exam', 'paused', 'completed', 'cancelled'],
            'ready_internal_exam' => ['ready_state_exam', 'practice', 'completed'],
            'ready_state_exam' => ['completed', 'practice'],
            'paused' => ['active', 'cancelled'],
            'completed' => ['archived'],
            'cancelled' => ['archived'],
            'expelled' => ['archived'],
        ];
    }
}
