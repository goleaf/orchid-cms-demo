<?php

namespace App\Rules;

use App\Models\Language;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidTrainingLanguageRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! filled($value)) {
            return;
        }

        if (Language::query()->where('code', (string) $value)->where('is_active', true)->exists()) {
            return;
        }

        $fail(tkey('students.validation.invalid_training_language'));
    }
}
