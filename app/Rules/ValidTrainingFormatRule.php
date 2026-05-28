<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidTrainingFormatRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! filled($value) || in_array((string) $value, ['offline', 'online', 'hybrid', 'individual', 'group'], true)) {
            return;
        }

        $fail(tkey('students.validation.invalid_training_format'));
    }
}
