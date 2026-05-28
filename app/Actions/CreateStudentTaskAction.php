<?php

namespace App\Actions;

use App\Enums\StudentTaskPriority;
use App\Enums\StudentTaskStatus;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\StudentTask;
use App\Models\User;
use Illuminate\Support\Carbon;

class CreateStudentTaskAction
{
    /**
     * @param  array<string, string>|string  $title
     * @param  array<string, string>|string|null  $description
     */
    public function handle(
        Student $student,
        array|string $title,
        ?User $createdBy = null,
        ?Carbon $dueAt = null,
        string $priority = StudentTaskPriority::Normal->value,
        array|string|null $description = null,
        ?int $assignedToId = null,
        ?StudentEnrollment $enrollment = null,
    ): StudentTask {
        $task = $student->tasks()->create([
            'enrollment_id' => $enrollment?->id,
            'title_translations' => $this->translations($title),
            'description_translations' => $description === null ? null : $this->translations($description),
            'assigned_to_id' => $assignedToId ?? $student->manager_id,
            'created_by_id' => $createdBy?->id,
            'priority' => $priority,
            'status' => StudentTaskStatus::Open->value,
            'due_at' => $dueAt,
            'completed_at' => null,
            'cancelled_at' => null,
        ]);

        app(RecordStudentActivityAction::class)->handle(
            $student,
            $createdBy,
            'task_created',
            tkey('students.activities.titles.task_created'),
            $task->display_title,
            null,
            null,
            ['task_id' => $task->id],
            $enrollment,
        );

        return $task;
    }

    /**
     * @param  array<string, string>|string  $value
     * @return array<string, string>
     */
    private function translations(array|string $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        return [
            'ru' => $value,
            'en' => $value,
            'lt' => $value,
            'pl' => $value,
        ];
    }
}
