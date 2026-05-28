<?php

namespace App\Rules;

use App\Models\LearningProgram;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class LearningProgramIsActiveRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! filled($value)) {
            return;
        }

        if (LearningProgram::query()->whereKey($value)->where('is_active', true)->exists()) {
            return;
        }

        $fail(tkey('education.groups.validation.learning_program_not_active'));
    }
}
