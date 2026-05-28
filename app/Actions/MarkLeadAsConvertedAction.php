<?php

namespace App\Actions;

use App\Enums\LeadStatus;
use App\Models\MarketingLead;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\User;

class MarkLeadAsConvertedAction
{
    public function handle(MarketingLead $lead, Student $student, StudentEnrollment $enrollment, ?User $user = null): MarketingLead
    {
        $lead->forceFill([
            'converted_at' => $lead->converted_at ?? now(),
            'converted_student_profile_id' => $student->id,
            'converted_enrollment_id' => $enrollment->id,
            'closed_at' => $lead->closed_at ?? now(),
            'status' => LeadStatus::Enrolled,
            'updated_by_user_id' => $user?->id ?? $lead->updated_by_user_id,
        ])->save();

        app(RecordLeadActivityAction::class)->handle(
            $lead->refresh(),
            $user,
            'converted',
            tkey('crm.activities.types.converted'),
            null,
            null,
            (string) $student->id,
            [
                'student_id' => $student->id,
                'enrollment_id' => $enrollment->id,
            ],
        );

        return $lead->refresh();
    }
}
