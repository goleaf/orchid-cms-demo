<?php

namespace App\Http\Requests\Marketing;

use App\Enums\LeadStatus;
use App\Enums\LeadTaskPriority;
use App\Models\Branch;
use App\Models\Instructor;
use App\Models\LeadLostReason;
use App\Models\LeadTag;
use App\Models\MarketingLead;
use App\Models\TrainingGroup;
use App\Models\TrainingProgram;
use App\Models\User;
use App\Rules\ActiveLeadSource;
use App\Rules\DifferentMarketingLead;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LeadCrmRequest extends FormRequest
{
    public function authorize(): bool
    {
        $permission = $this->leadId() === null
            ? 'crm.leads.create'
            : 'crm.leads.update';

        return $this->user()?->hasAnyAccess([$permission, 'platform.marketing.leads', 'website.update_leads']) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $leadId = $this->leadId();

        return [
            'lead.id' => ['nullable', 'integer', Rule::exists(MarketingLead::class, 'id')],
            'lead.responsible_manager_id' => ['nullable', 'integer', Rule::exists(User::class, 'id')],
            'lead.branch_id' => ['nullable', 'integer', Rule::exists(Branch::class, 'id')],
            'lead.training_program_id' => ['nullable', 'integer', Rule::exists(TrainingProgram::class, 'id')],
            'lead.training_group_id' => ['nullable', 'integer', Rule::exists(TrainingGroup::class, 'id')],
            'lead.instructor_id' => ['nullable', 'integer', Rule::exists(Instructor::class, 'id')],
            'lead.first_name' => ['required', 'string', 'max:120'],
            'lead.last_name' => ['nullable', 'string', 'max:120'],
            'lead.email' => ['nullable', 'required_without:lead.phone', 'email:rfc', 'max:190'],
            'lead.phone' => ['nullable', 'required_without:lead.email', 'string', 'max:60'],
            'lead.messenger' => ['nullable', 'string', 'max:80'],
            'lead.city' => ['nullable', 'string', 'max:120'],
            'lead.source' => ['required', 'string', 'max:120', new ActiveLeadSource],
            'lead_status' => ['required', Rule::enum(LeadStatus::class)],
            'lead.license_category' => ['nullable', 'string', 'max:40'],
            'lead.preferred_format' => ['nullable', 'string', 'max:60'],
            'lead.preferred_language' => ['nullable', 'string', 'max:60'],
            'lead.preferred_time' => ['nullable', 'string', 'max:120'],
            'lead.is_hot' => ['nullable', 'boolean'],
            'lead.priority' => ['required', Rule::enum(LeadTaskPriority::class)],
            'lead.lead_score' => ['nullable', 'integer', 'min:0', 'max:100'],
            'lead.last_contacted_at' => ['nullable', 'date'],
            'lead.next_follow_up_at' => ['nullable', 'date'],
            'lead.message' => ['nullable', 'string', 'max:2000'],
            'lead.internal_comment' => ['nullable', 'string', 'max:2000'],
            'lead.lost_reason_code' => ['nullable', 'string', Rule::in(array_keys(LeadLostReason::translatedLabels()))],
            'lead.rejection_reason' => ['nullable', 'string', 'max:2000'],
            'lead.duplicate_of_id' => [
                'nullable',
                'integer',
                Rule::exists(MarketingLead::class, 'id'),
                new DifferentMarketingLead($leadId),
            ],
            'lead.tag_ids' => ['nullable', 'array'],
            'lead.tag_ids.*' => ['integer', Rule::exists(LeadTag::class, 'id')],
            'lead.consent_accepted' => ['nullable', 'boolean'],
            'lead.consent_text_version' => ['nullable', 'string', 'max:120'],
            'lead_budget_eur' => ['nullable', 'numeric', 'min:0', 'max:100000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'lead.first_name.required' => tkey('crm.validation.first_name_required'),
            'lead.email.required_without' => tkey('crm.validation.contact_required'),
            'lead.phone.required_without' => tkey('crm.validation.contact_required'),
            'lead.email.email' => tkey('crm.validation.email_invalid'),
            'lead.source.required' => tkey('crm.validation.source_required'),
            'lead_status.required' => tkey('crm.validation.status_required'),
            'lead.priority.required' => tkey('crm.validation.priority_required'),
            'lead.lead_score.max' => tkey('crm.validation.lead_score_range'),
            'lead_budget_eur.max' => tkey('crm.validation.budget_range'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function leadData(): array
    {
        $lead = $this->validated('lead');
        unset($lead['id'], $lead['tag_ids']);

        foreach ([
            'responsible_manager_id',
            'branch_id',
            'training_program_id',
            'training_group_id',
            'instructor_id',
            'duplicate_of_id',
        ] as $key) {
            $lead[$key] = $this->nullableInteger($lead[$key] ?? null);
        }

        $lead['is_hot'] = (bool) ($lead['is_hot'] ?? false);
        $lead['consent_accepted'] = (bool) ($lead['consent_accepted'] ?? false);
        $lead['lead_score'] = filled($lead['lead_score'] ?? null) ? (int) $lead['lead_score'] : 0;

        return $lead;
    }

    public function leadId(): ?int
    {
        $value = $this->input('lead.id');

        return filled($value) ? (int) $value : null;
    }

    public function targetStatus(): LeadStatus
    {
        return LeadStatus::from($this->validated('lead_status'));
    }

    /**
     * @return array<int, int>
     */
    public function tagIds(): array
    {
        return collect($this->validated('lead.tag_ids', []))
            ->map(fn (mixed $id): int => (int) $id)
            ->values()
            ->all();
    }

    public function budgetEur(): mixed
    {
        return $this->validated('lead_budget_eur', null);
    }

    private function nullableInteger(mixed $value): ?int
    {
        return filled($value) ? (int) $value : null;
    }
}
