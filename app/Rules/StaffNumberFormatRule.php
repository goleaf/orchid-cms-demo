<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class StaffNumberFormatRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (blank($value) || preg_match('/^STAFF-\d{4}-\d{4,}$/', (string) $value) === 1) {
            return;
        }

        $fail(tkey('security.validation.staff_number_format'));
    }
}
