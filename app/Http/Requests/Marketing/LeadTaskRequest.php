<?php

namespace App\Http\Requests\Marketing;

use App\Enums\LeadTaskPriority;
use App\Models\User;
use App\Rules\ValidLeadTaskPriorityRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class LeadTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyAccess(['crm.leads.manage_tasks', 'crm.leads.update', 'platform.marketing.leads']) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'task.title' => ['required', 'string', 'max:190'],
            'task.notes' => ['nullable', 'string', 'max:2000'],
            'task.assigned_to_user_id' => ['nullable', 'integer', Rule::exists(User::class, 'id')],
            'task.priority' => ['required', Rule::enum(LeadTaskPriority::class), new ValidLeadTaskPriorityRule],
            'task.due_at' => ['nullable', 'date'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'task.title.required' => tkey('crm.validation.task_title_required'),
            'task.priority.required' => tkey('crm.validation.priority_required'),
            'task.due_at.date' => tkey('crm.validation.task_due_at_invalid'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function taskData(): array
    {
        return $this->validated('task');
    }

    public function priority(): LeadTaskPriority
    {
        return LeadTaskPriority::from($this->validated('task.priority'));
    }

    public function dueAt(): ?Carbon
    {
        $value = $this->validated('task.due_at', null);

        return filled($value) ? Carbon::parse($value) : null;
    }

    public function assignedToUserId(): ?int
    {
        $value = $this->validated('task.assigned_to_user_id', null);

        return filled($value) ? (int) $value : null;
    }
}
