<?php

namespace App\Rules;

use App\Enums\CourseFormat;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidTrainingFormatRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! filled($value) || in_array((string) $value, CourseFormat::values(), true)) {
            return;
        }

        $fail(tkey('students.validation.invalid_training_format'));
    }
}
