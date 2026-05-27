<?php

namespace App\Http\Requests\Marketing;

use App\Models\MarketingLeadTask;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CancelLeadTaskRequest extends FormRequest
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
            'reason' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function taskId(): int
    {
        return (int) $this->validated('task');
    }

    public function reason(): ?string
    {
        $reason = $this->validated('reason', null);

        return filled($reason) ? (string) $reason : null;
    }
}
