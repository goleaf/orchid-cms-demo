<?php

namespace App\Rules;

use App\Models\Language;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;

class TranslatedPermissionNameRequiredRule implements DataAwareRule, ValidationRule
{
    /**
     * @var array<string, mixed>
     */
    private array $data = [];

    public function __construct(private readonly string $translationsField = 'name_translations') {}

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
        $defaultLocale = Language::defaultCode();
        $translations = is_array($value) ? $value : data_get($this->data, $this->translationsField, []);

        if (is_array($translations) && filled($translations[$defaultLocale] ?? null)) {
            return;
        }

        $fail(tkey('security.validation.default_permission_name_required'));
    }
}
