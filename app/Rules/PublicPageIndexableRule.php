<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;

class PublicPageIndexableRule implements DataAwareRule, ValidationRule
{
    /**
     * @var array<string, mixed>
     */
    private array $data = [];

    /**
     * @param  array<string, mixed>  $data
     */
    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! filter_var($value, FILTER_VALIDATE_BOOLEAN)) {
            return;
        }

        $isActive = $this->booleanValue('is_active');
        $isVisibleOnSite = $this->booleanValue('is_visible_on_site');

        if ($isActive === false || $isVisibleOnSite === false) {
            $fail(tkey('website.validation.public_page_not_indexable'));
        }
    }

    private function booleanValue(string $key): ?bool
    {
        if (! array_key_exists($key, $this->data)) {
            return null;
        }

        return filter_var($this->data[$key], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    }
}
