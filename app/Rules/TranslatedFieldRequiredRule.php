<?php

namespace App\Rules;

use App\Services\LocaleManager;
use App\Services\TranslatableContentManager;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class TranslatedFieldRequiredRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $translations = is_array($value) ? $value : [];
        $defaultLocale = app(LocaleManager::class)->defaultLocale();

        if (! app(TranslatableContentManager::class)->isMissingValue($translations[$defaultLocale] ?? null)) {
            return;
        }

        $fail(tkey('website.validation.default_translation_required'));
    }
}
