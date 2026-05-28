<?php

namespace App\Http\Requests\Students;

use App\Models\StudentEnrollment;
use App\Models\User;
use App\Rules\TranslatedStudentTaskTitleRequiredRule;
use App\Rules\ValidStudentTaskPriorityRule;
use App\Rules\ValidStudentTaskStatusRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StudentTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyAccess(['platform.crm.students', 'students.tasks.manage']) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'task.enrollment_id' => ['nullable', 'integer', Rule::exists(StudentEnrollment::class, 'id')],
            'task.title_translations' => ['required', 'array', new TranslatedStudentTaskTitleRequiredRule],
            'task.title_translations.*' => ['nullable', 'string', 'max:240'],
            'task.description_translations' => ['nullable', 'array'],
            'task.description_translations.*' => ['nullable', 'string', 'max:2000'],
            'task.assigned_to_id' => ['nullable', 'integer', Rule::exists(User::class, 'id')],
            'task.priority' => ['required', 'string', new ValidStudentTaskPriorityRule],
            'task.status' => ['nullable', 'string', new ValidStudentTaskStatusRule],
            'task.due_at' => ['nullable', 'date'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function taskData(): array
    {
        $task = $this->validated('task');

        foreach (['enrollment_id', 'assigned_to_id'] as $field) {
            if (array_key_exists($field, $task)) {
                $task[$field] = filled($task[$field]) ? (int) $task[$field] : null;
            }
        }

        return $task;
    }
}
