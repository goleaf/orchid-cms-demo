<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class DictionaryCodeRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (filled($value) && preg_match('/^[a-z0-9]+(?:[_-][a-z0-9]+)*$/', (string) $value) === 1) {
            return;
        }

        $fail(tkey('crm.leads.validation.invalid_dictionary_code'));
    }
}
