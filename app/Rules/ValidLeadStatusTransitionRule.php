<?php

namespace App\Rules;

use App\Actions\ChangeLeadStatusAction;
use App\Enums\LeadStatus;
use App\Models\MarketingLead;
use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use ValueError;

class ValidLeadStatusTransitionRule implements ValidationRule
{
    public function __construct(
        private readonly ?MarketingLead $lead,
        private readonly ?User $user = null,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($this->lead === null || ! filled($value)) {
            return;
        }

        try {
            $targetStatus = $value instanceof LeadStatus ? $value : LeadStatus::from((string) $value);
        } catch (ValueError) {
            $fail(tkey('crm.leads.validation.invalid_status_transition'));

            return;
        }

        if ($this->user?->hasAccess('crm.leads.override_status_transition')) {
            return;
        }

        if (! app(ChangeLeadStatusAction::class)->transitionAllowed($this->lead->status, $targetStatus)) {
            $fail(tkey('crm.leads.validation.invalid_status_transition'));
        }
    }
}
