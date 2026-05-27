<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SeoMetadataRule implements ValidationRule
{
    public function __construct(private readonly int $max = 180) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $values = is_array($value) ? $value : [$value];

        foreach ($values as $item) {
            if (blank($item)) {
                continue;
            }

            if (! is_scalar($item) || mb_strlen((string) $item) > $this->max) {
                $fail(tkey('website.validation.invalid_seo_metadata'));

                return;
            }
        }
    }
}
