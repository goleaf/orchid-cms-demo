<?php

namespace App\Rules;

use App\Models\LeadLostReason;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ActiveLeadLostReasonRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (
            filled($value)
            && LeadLostReason::query()
                ->active()
                ->where('code', (string) $value)
                ->exists()
        ) {
            return;
        }

        $fail(tkey('crm.leads.validation.lost_reason_not_active'));
    }
}
