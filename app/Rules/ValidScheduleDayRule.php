<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidScheduleDayRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_numeric($value) || (int) $value < 1 || (int) $value > 7) {
            $fail(tkey('education.validation.invalid_schedule_day'));
        }
    }
}
