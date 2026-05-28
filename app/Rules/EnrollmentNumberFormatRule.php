<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class EnrollmentNumberFormatRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! filled($value) || preg_match('/^ENR-\d{4}-\d{4,}$/', (string) $value) === 1) {
            return;
        }

        $fail(tkey('students.validation.invalid_enrollment_number'));
    }
}
