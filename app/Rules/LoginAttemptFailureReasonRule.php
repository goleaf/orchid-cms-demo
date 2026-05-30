<?php

namespace App\Rules;

use App\Models\LoginAttempt;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class LoginAttemptFailureReasonRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (blank($value) || in_array($value, LoginAttempt::FAILURE_REASONS, true)) {
            return;
        }

        $fail(tkey('security.validation.invalid_login_failure_reason'));
    }
}
