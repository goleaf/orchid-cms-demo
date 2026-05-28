<?php

namespace App\Actions;

use App\Models\Student;
use App\Models\User;

class CreatePortalAccessPlaceholderAction
{
    public function handle(Student $student, ?User $user = null): Student
    {
        $student->forceFill([
            'portal_access_created_at' => $student->portal_access_created_at ?? now(),
            'updated_by_id' => $user?->id ?? $student->updated_by_id,
        ])->save();

        app(RecordStudentActivityAction::class)->handle(
            $student->refresh(),
            $user,
            'portal_access_created',
            tkey('students.activities.titles.portal_access_created'),
            tkey('students.activities.messages.portal_access_placeholder'),
        );

        return $student->refresh();
    }
}
