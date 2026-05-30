<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class FailedLoginThresholdRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (filter_var($value, FILTER_VALIDATE_INT) !== false && (int) $value >= 1 && (int) $value <= 100) {
            return;
        }

        $fail(tkey('security.validation.failed_login_threshold_exceeded'));
    }
}
