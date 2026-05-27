<?php

namespace App\Http\Requests\Marketing;

use App\Enums\LeadStatus;
use App\Rules\ActiveLeadStatusRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReopenLeadRequest extends FormRequest
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
            'status' => ['nullable', Rule::enum(LeadStatus::class), new ActiveLeadStatusRule],
            'reason' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function status(): ?LeadStatus
    {
        $status = $this->validated('status', null);

        return filled($status) ? LeadStatus::from((string) $status) : null;
    }

    public function reason(): ?string
    {
        $reason = $this->validated('reason', null);

        return filled($reason) ? (string) $reason : null;
    }
}
