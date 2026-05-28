<?php

namespace App\Actions;

use App\Models\MarketingLead;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class LinkLeadToExistingStudentAction
{
    /**
     * @param  array<string, mixed>  $enrollmentData
     * @return array<string, mixed>
     */
    public function handle(MarketingLead $lead, int $studentId, array $enrollmentData, ?User $user = null): array
    {
        if (blank($studentId)) {
            throw ValidationException::withMessages([
                'existing_student_id' => tkey('students.conversion.validation.existing_student_invalid'),
            ]);
        }

        return app(ConvertLeadToStudentAction::class)->handle(
            $lead,
            $studentId,
            [],
            $enrollmentData,
            true,
            true,
            true,
            $user,
        );
    }
}
