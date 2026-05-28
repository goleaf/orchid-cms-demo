<?php

namespace App\Actions;

use App\Models\MarketingLead;
use App\Models\Student;
use App\Models\StudentActivity;
use App\Models\StudentEnrollment;
use App\Models\User;

class RecordStudentActivityAction
{
    /**
     * @param  array<string, mixed>|null  $meta
     */
    public function handle(
        Student $student,
        ?User $user,
        string $type,
        ?string $title = null,
        ?string $body = null,
        ?string $oldValue = null,
        ?string $newValue = null,
        ?array $meta = null,
        ?StudentEnrollment $enrollment = null,
        ?MarketingLead $lead = null,
    ): StudentActivity {
        return $student->activities()->create([
            'enrollment_id' => $enrollment?->id,
            'lead_id' => $lead?->id,
            'user_id' => $user?->id,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'meta' => $meta,
        ]);
    }
}
