<?php

namespace App\Rules;

use App\Models\MarketingLead;
use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class LeadCanBeUpdatedRule implements ValidationRule
{
    public function __construct(
        private readonly ?MarketingLead $lead,
        private readonly ?User $user = null,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($this->lead === null || $this->user?->hasAccess('crm.leads.override_status_transition')) {
            return;
        }

        if ($this->lead->is_converted) {
            $fail(tkey('crm.leads.validation.lead_already_converted'));

            return;
        }

        if ($this->lead->is_spam) {
            $fail(tkey('crm.leads.validation.lead_is_spam'));

            return;
        }

        if ($this->lead->is_duplicate) {
            $fail(tkey('crm.leads.validation.lead_is_duplicate'));

            return;
        }

        if ($this->lead->is_lost) {
            $fail(tkey('crm.leads.validation.lead_is_lost'));
        }
    }
}
