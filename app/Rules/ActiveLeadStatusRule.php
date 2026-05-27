<?php

namespace App\Rules;

use App\Models\LeadStatus;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ActiveLeadStatusRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (
            filled($value)
            && LeadStatus::query()
                ->active()
                ->where('code', (string) $value)
                ->exists()
        ) {
            return;
        }

        $fail(tkey('crm.leads.validation.status_not_active'));
    }
}
