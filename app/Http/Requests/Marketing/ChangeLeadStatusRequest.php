<?php

namespace App\Http\Requests\Marketing;

use App\Enums\LeadStatus;
use App\Models\MarketingLead;
use App\Rules\ActiveLeadLostReasonRule;
use App\Rules\ActiveLeadStatusRule;
use App\Rules\ValidLeadStatusTransitionRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChangeLeadStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyAccess(['crm.leads.change_status', 'crm.leads.update', 'platform.marketing.leads']) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => [
                'required',
                Rule::enum(LeadStatus::class),
                new ActiveLeadStatusRule,
                new ValidLeadStatusTransitionRule($this->lead(), $this->user(), $this->boolean('override_status_transition')),
            ],
            'override_status_transition' => ['nullable', 'boolean'],
            'reason' => ['nullable', 'string', 'max:2000'],
            'lost_reason_code' => ['nullable', 'string', new ActiveLeadLostReasonRule],
        ];
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

    public function lostReasonCode(): ?string
    {
        $reason = $this->validated('lost_reason_code', null);

        return filled($reason) ? (string) $reason : null;
    }

    private function lead(): ?MarketingLead
    {
        $lead = $this->route('lead');

        return $lead instanceof MarketingLead ? $lead : null;
    }
}
