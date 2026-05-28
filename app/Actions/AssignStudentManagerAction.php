<?php

namespace App\Actions;

use App\Models\Student;
use App\Models\User;

class AssignStudentManagerAction
{
    public function handle(Student $student, ?int $managerId, ?User $user = null, bool $assignOpenTasks = true): Student
    {
        $oldManagerId = $student->manager_id;

        $student->forceFill([
            'manager_id' => $managerId,
            'updated_by_id' => $user?->id ?? $student->updated_by_id,
        ])->save();

        if ($assignOpenTasks && $managerId !== null) {
            $student->tasks()
                ->open()
                ->whereNull('assigned_to_id')
                ->update(['assigned_to_id' => $managerId]);
        }

        app(RecordStudentActivityAction::class)->handle(
            $student->refresh(),
            $user,
            'manager_assigned',
            tkey('students.activities.titles.manager_assigned'),
            null,
            filled($oldManagerId) ? (string) $oldManagerId : null,
            filled($managerId) ? (string) $managerId : null,
        );

        return $student->refresh();
    }
}
