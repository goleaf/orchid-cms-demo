<?php

namespace App\Rules;

use App\Models\LeadSource;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ActiveLeadSource implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (
            filled($value)
            && LeadSource::query()
                ->active()
                ->where('code', (string) $value)
                ->exists()
        ) {
            return;
        }

        $fail(tkey('crm.validation.source_unavailable'));
    }
}
