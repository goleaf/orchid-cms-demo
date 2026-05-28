<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidDayOfWeekRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (is_numeric($value) && (int) $value >= 1 && (int) $value <= 7) {
            return;
        }

        $fail(tkey('education.groups.validation.invalid_day_of_week'));
    }
}
