<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class KpiTargetValueRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (is_numeric($value) && is_finite((float) $value) && (float) $value >= 0.0) {
            return;
        }

        $fail(tkey('analytics.validation.invalid_target_value'));
    }
}
