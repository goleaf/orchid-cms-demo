<?php

namespace App\Actions;

use App\Models\Student;
use App\Models\StudentActivity;
use App\Models\StudentEnrollment;
use App\Models\User;

class AddStudentNoteAction
{
    public function handle(Student $student, string $body, ?User $user = null, ?StudentEnrollment $enrollment = null): StudentActivity
    {
        return app(RecordStudentActivityAction::class)->handle(
            $student,
            $user,
            'note_added',
            tkey('students.activities.titles.note_added'),
            $body,
            null,
            null,
            null,
            $enrollment,
        );
    }
}
