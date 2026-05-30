<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class StaffPhoneRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (blank($value)) {
            return;
        }

        $phone = trim((string) $value);

        if (preg_match('/^\+?[0-9 ()-]{6,32}$/', $phone) === 1) {
            return;
        }

        $fail(tkey('security.validation.invalid_staff_phone'));
    }
}
