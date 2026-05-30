<?php

namespace App\Rules;

use Closure;
use DateTimeZone;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidUserTimezoneRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (blank($value) || in_array((string) $value, DateTimeZone::listIdentifiers(), true)) {
            return;
        }

        $fail(tkey('security.validation.invalid_user_timezone'));
    }
}
