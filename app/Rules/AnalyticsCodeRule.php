<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class AnalyticsCodeRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (is_string($value) && preg_match('/^[a-z0-9]+(?:[_-][a-z0-9]+)*$/', $value) === 1) {
            return;
        }

        $fail(tkey('analytics.validation.invalid_code'));
    }
}
