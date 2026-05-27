<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidCanonicalUrlRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (blank($value)) {
            return;
        }

        if (! is_string($value) || filter_var($value, FILTER_VALIDATE_URL) === false) {
            $fail(tkey('website.validation.invalid_canonical_url'));

            return;
        }

        $scheme = parse_url($value, PHP_URL_SCHEME);

        if (! in_array($scheme, ['http', 'https'], true)) {
            $fail(tkey('website.validation.invalid_canonical_url'));
        }
    }
}
