<?php

namespace App\Actions;

use App\Models\StudentTask;
use App\Models\User;

class CancelStudentTaskAction
{
    public function handle(StudentTask $task, ?User $user = null): StudentTask
    {
        $task->forceFill([
            'status' => 'cancelled',
            'cancelled_at' => $task->cancelled_at ?? now(),
        ])->save();

        app(RecordStudentActivityAction::class)->handle(
            $task->student,
            $user,
            'task_cancelled',
            tkey('students.activities.titles.task_cancelled'),
            $task->display_title,
            null,
            null,
            ['task_id' => $task->id],
            $task->enrollment,
        );

        return $task->refresh();
    }
}
