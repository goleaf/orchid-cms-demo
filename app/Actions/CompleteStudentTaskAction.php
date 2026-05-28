<?php

namespace App\Actions;

use App\Models\StudentTask;
use App\Models\User;

class CompleteStudentTaskAction
{
    public function handle(StudentTask $task, ?User $user = null): StudentTask
    {
        $task->forceFill([
            'status' => 'done',
            'completed_at' => $task->completed_at ?? now(),
            'cancelled_at' => null,
        ])->save();

        app(RecordStudentActivityAction::class)->handle(
            $task->student,
            $user,
            'task_completed',
            tkey('students.activities.titles.task_completed'),
            $task->display_title,
            null,
            null,
            ['task_id' => $task->id],
            $task->enrollment,
        );

        return $task->refresh();
    }
}
