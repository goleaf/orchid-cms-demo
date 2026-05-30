<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SessionIdHashRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (blank($value) || (is_string($value) && preg_match('/^[a-f0-9]{64}$/', $value) === 1)) {
            return;
        }

        $fail(tkey('security.validation.invalid_session_id_hash'));
    }
}
