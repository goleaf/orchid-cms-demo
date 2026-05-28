<?php

namespace App\Rules;

use App\Services\LocaleManager;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class TranslatedLearningProgramNameRequiredRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $defaultLocale = app(LocaleManager::class)->defaultLocale();

        if (is_array($value) && filled($value[$defaultLocale] ?? null)) {
            return;
        }

        $fail(tkey('education.groups.validation.default_learning_program_name_required'));
    }
}
