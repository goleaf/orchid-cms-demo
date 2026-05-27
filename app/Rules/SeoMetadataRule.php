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
                $fail(tkey($this->messageKey($attribute)));

                return;
            }
        }
    }

    private function messageKey(string $attribute): string
    {
        if (str_contains($attribute, 'seo_title')) {
            return 'website.validation.seo_title_too_long';
        }

        if (str_contains($attribute, 'seo_description')) {
            return 'website.validation.seo_description_too_long';
        }

        return 'website.validation.invalid_seo_metadata';
    }
}
