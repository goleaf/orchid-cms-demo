<?php

namespace App\Http\Requests\Marketing;

use App\Models\MarketingLead;
use App\Rules\LeadDuplicateOriginalRule;
use App\Rules\LeadIsNotDuplicateOfItselfRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LeadDuplicateRequest extends FormRequest
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
        $lead = $this->route('lead');
        $leadId = is_object($lead) ? $lead->getKey() : null;

        return [
            'duplicate.original_id' => [
                'required',
                'integer',
                Rule::exists(MarketingLead::class, 'id'),
                new LeadIsNotDuplicateOfItselfRule($leadId),
                new LeadDuplicateOriginalRule($leadId),
            ],
            'duplicate.comment' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'duplicate.original_id.required' => tkey('crm.validation.duplicate_original_required'),
            'duplicate.original_id.exists' => tkey('crm.validation.duplicate_original_unavailable'),
            'duplicate.comment.max' => tkey('crm.validation.comment_too_long'),
        ];
    }

    public function originalId(): int
    {
        return (int) $this->validated('duplicate.original_id');
    }

    public function comment(): ?string
    {
        $comment = $this->validated('duplicate.comment', null);

        return filled($comment) ? (string) $comment : null;
    }
}
