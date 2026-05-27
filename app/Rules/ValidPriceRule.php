<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidPriceRule implements ValidationRule
{
    public function __construct(private readonly bool $nullable = true) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (blank($value) && $this->nullable) {
            return;
        }

        if (is_numeric($value) && (float) $value > 0) {
            return;
        }

        $fail(tkey('website.validation.invalid_price'));
    }
}
