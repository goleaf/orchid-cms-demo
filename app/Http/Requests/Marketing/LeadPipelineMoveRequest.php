<?php

namespace App\Http\Requests\Marketing;

use App\Enums\LeadStatus;
use App\Models\MarketingLead;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LeadPipelineMoveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyAccess(['crm.leads.change_status', 'platform.marketing.pipeline']) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'lead_id' => ['required', 'integer', Rule::exists(MarketingLead::class, 'id')],
            'status' => ['required', Rule::enum(LeadStatus::class)],
            'reason' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'lead_id.required' => tkey('crm.validation.lead_required'),
            'lead_id.exists' => tkey('crm.validation.lead_unavailable'),
            'status.required' => tkey('crm.validation.status_required'),
            'reason.max' => tkey('crm.validation.reason_too_long'),
        ];
    }

    public function leadId(): int
    {
        return (int) $this->validated('lead_id');
    }

    public function status(): LeadStatus
    {
        return LeadStatus::from($this->validated('status'));
    }

    public function reason(): ?string
    {
        $reason = $this->validated('reason', null);

        return filled($reason) ? (string) $reason : null;
    }
}
