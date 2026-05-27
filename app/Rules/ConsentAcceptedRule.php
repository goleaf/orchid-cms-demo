<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ConsentAcceptedRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (filter_var($value, FILTER_VALIDATE_BOOLEAN)) {
            return;
        }

        $fail(tkey('website.validation.consent_required'));
    }
}
