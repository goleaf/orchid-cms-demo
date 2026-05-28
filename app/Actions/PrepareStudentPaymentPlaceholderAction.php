<?php

namespace App\Actions;

use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\User;

class PrepareStudentPaymentPlaceholderAction
{
    public function handle(Student $student, ?User $user = null, ?StudentEnrollment $enrollment = null): Student
    {
        $enrollment ??= $student->current_enrollment;
        $status = $enrollment?->payment_status ?: 'pending';
        $summary = [
            'status' => $status,
            'payment_status' => $status,
            'expected_price' => $enrollment?->price,
            'currency' => $enrollment?->currency ?: 'EUR',
            'enrollment_id' => $enrollment?->id,
        ];

        $student->forceFill([
            'payment_summary' => $summary,
            'updated_by_id' => $user?->id ?? $student->updated_by_id,
        ])->save();

        app(RecordStudentActivityAction::class)->handle(
            $student->refresh(),
            $user,
            'payment_placeholder_created',
            tkey('students.activities.titles.payment_placeholder_created'),
            null,
            null,
            null,
            $summary,
            $enrollment,
        );

        return $student->refresh();
    }
}
