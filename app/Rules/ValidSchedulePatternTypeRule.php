<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidSchedulePatternTypeRule implements ValidationRule
{
    public const TYPES = ['theory', 'practice', 'consultation', 'exam_preparation', 'other'];

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (filled($value) && in_array((string) $value, self::TYPES, true)) {
            return;
        }

        $fail(tkey('education.groups.validation.invalid_schedule_pattern_type'));
    }
}
