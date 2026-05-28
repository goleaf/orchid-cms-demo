<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidLearningProgramModuleTypeRule implements ValidationRule
{
    public const TYPES = ['theory', 'practice', 'exam_preparation', 'internal_exam', 'state_exam_preparation', 'documents', 'onboarding', 'other'];

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (filled($value) && in_array((string) $value, self::TYPES, true)) {
            return;
        }

        $fail(tkey('education.groups.validation.invalid_module_type'));
    }
}
