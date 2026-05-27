<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class DifferentMarketingLead implements ValidationRule
{
    public function __construct(private readonly mixed $currentLeadId) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! filled($value) || ! filled($this->currentLeadId)) {
            return;
        }

        if ((int) $value !== (int) $this->currentLeadId) {
            return;
        }

        $fail(tkey('crm.leads.validation.duplicate_self'));
    }
}
