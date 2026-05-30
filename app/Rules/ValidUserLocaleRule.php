<?php

namespace App\Rules;

use App\Services\LocaleManager;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidUserLocaleRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (blank($value) || app(LocaleManager::class)->isActiveLocale($value)) {
            return;
        }

        $fail(tkey('security.validation.invalid_user_locale'));
    }
}
