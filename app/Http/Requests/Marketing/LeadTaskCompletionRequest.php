<?php

namespace App\Http\Requests\Marketing;

use App\Models\MarketingLeadTask;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LeadTaskCompletionRequest extends FormRequest
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
            'task' => ['required', 'integer', Rule::exists(MarketingLeadTask::class, 'id')],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'task.required' => tkey('crm.validation.task_required'),
            'task.exists' => tkey('crm.validation.task_unavailable'),
        ];
    }

    public function taskId(): int
    {
        return (int) $this->validated('task');
    }
}
