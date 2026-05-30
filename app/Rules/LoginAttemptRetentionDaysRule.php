<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class LoginAttemptRetentionDaysRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (filter_var($value, FILTER_VALIDATE_INT) !== false && (int) $value >= 1 && (int) $value <= 3650) {
            return;
        }

        $fail(tkey('security.validation.invalid_login_attempt_retention_days'));
    }
}
