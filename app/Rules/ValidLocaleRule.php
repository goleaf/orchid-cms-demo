<?php

namespace App\Rules;

use App\Services\LocaleManager;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidLocaleRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! filled($value) || app(LocaleManager::class)->isActiveLocale($value)) {
            return;
        }

        $fail(tkey('website.validation.invalid_locale'));
    }
}
