<?php

namespace App\Actions;

use App\Enums\StudentStatus as StudentStatusEnum;
use App\Models\Student;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class ArchiveStudentAction
{
    public function handle(Student $student, ?User $user = null, bool $allowActiveEnrollment = false): Student
    {
        $override = $allowActiveEnrollment || ($user?->hasAccess('students.archive_with_active_enrollment') ?? false);

        if (! $override && $student->activeEnrollments()->exists()) {
            throw ValidationException::withMessages([
                'student' => tkey('students.validation.student_cannot_be_archived'),
            ]);
        }

        if ($student->status !== StudentStatusEnum::Archived) {
            $student = app(ChangeStudentStatusAction::class)->handle(
                $student,
                StudentStatusEnum::Archived,
                $user,
                true,
            );
        }

        app(RecordStudentActivityAction::class)->handle(
            $student->refresh(),
            $user,
            'archived',
            tkey('students.activities.titles.archived'),
        );

        return $student->refresh();
    }
}
