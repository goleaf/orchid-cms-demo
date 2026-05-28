<?php

namespace App\Rules;

use App\Models\Language;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class TranslatedCommunicationFieldRequiredRule implements ValidationRule
{
    public function __construct(private readonly ?string $messageKey = null) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $translations = is_array($value) ? $value : [];

        if (filled($translations[Language::defaultCode()] ?? null)) {
            return;
        }

        $fail(tkey($this->messageKey ?? 'communication.validation.default_translation_required'));
    }
}
