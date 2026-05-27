<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class LeadIsNotDuplicateOfItselfRule implements ValidationRule
{
    public function __construct(private readonly mixed $currentLeadId) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! filled($value) || ! filled($this->currentLeadId)) {
            return;
        }

        if ((int) $value === (int) $this->currentLeadId) {
            $fail(tkey('crm.leads.validation.cannot_duplicate_itself'));
        }
    }
}
