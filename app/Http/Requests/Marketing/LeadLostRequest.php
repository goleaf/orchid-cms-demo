<?php

namespace App\Http\Requests\Marketing;

use App\Models\LeadLostReason;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LeadLostRequest extends FormRequest
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
            'lost.reason' => ['required', 'string', Rule::in(array_keys(LeadLostReason::translatedLabels()))],
            'lost.comment' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'lost.reason.required' => tkey('crm.validation.lost_reason_required'),
            'lost.reason.in' => tkey('crm.validation.lost_reason_unavailable'),
            'lost.comment.max' => tkey('crm.validation.comment_too_long'),
        ];
    }

    public function reason(): string
    {
        return (string) $this->validated('lost.reason');
    }

    public function comment(): ?string
    {
        $comment = $this->validated('lost.comment', null);

        return filled($comment) ? (string) $comment : null;
    }
}
