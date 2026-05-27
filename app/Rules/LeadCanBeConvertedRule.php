<?php

namespace App\Rules;

use App\Models\MarketingLead;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class LeadCanBeConvertedRule implements ValidationRule
{
    public function __construct(private readonly ?MarketingLead $lead) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($this->lead === null || $this->lead->can_be_converted) {
            return;
        }

        $fail(tkey('crm.leads.validation.lead_cannot_be_converted'));
    }
}
