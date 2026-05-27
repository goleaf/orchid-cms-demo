<?php

namespace App\Services;

use Illuminate\Http\Request;

class TranslatableContentManager
{
    public const COPY_KEY = '_translatable_copy';

    public function __construct(private readonly LocaleManager $locales) {}

    /**
     * @param  array<int, string>  $fields
     * @return array<string, array<string, mixed>>
     */
    public function extract(Request|array $source, array $fields): array
    {
        $payload = $source instanceof Request ? $source->all() : $source;
        $result = [];

        foreach ($fields as $field) {
            $baseField = $this->baseField($field);
            $attribute = $this->translationAttribute($baseField);
            $values = data_get($payload, $attribute, []);
            $copyFlags = data_get($payload, self::COPY_KEY.'.'.$baseField, []);

            $result[$attribute] = $this->normalizeField(
                is_array($values) ? $values : [],
                is_array($copyFlags) ? $copyFlags : [],
            );
        }

        return $result;
    }

    /**
     * @param  array<int, string>  $fields
     * @param  array<int, string>|string  $valueRules
     * @return array<string, mixed>
     */
    public function validationRules(array $fields, array|string $valueRules = ['nullable', 'string']): array
    {
        $rules = [
            self::COPY_KEY => ['nullable', 'array'],
            self::COPY_KEY.'.*' => ['nullable', 'array'],
            self::COPY_KEY.'.*.*' => ['nullable', 'boolean'],
        ];

        foreach ($fields as $field) {
            $attribute = $this->translationAttribute($field);
            $rules[$attribute] = ['nullable', 'array'];
            $rules[$attribute.'.*'] = $valueRules;
        }

        return $rules;
    }

    /**
     * @param  array<string, mixed>  $translations
     * @return array<int, string>
     */
    public function missingTranslations(array $translations, bool $includeDefault = false): array
    {
        $defaultLocale = $this->locales->defaultLocale();

        return collect($this->locales->activeCodes())
            ->reject(fn (string $code): bool => ! $includeDefault && $code === $defaultLocale)
            ->filter(fn (string $code): bool => $this->isMissingValue($translations[$code] ?? null))
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $translations
     */
    public function defaultValue(array $translations): mixed
    {
        return $translations[$this->locales->defaultLocale()] ?? null;
    }

    public function isMissingValue(mixed $value): bool
    {
        if (is_bool($value)) {
            return false;
        }

        if ($value === null) {
            return true;
        }

        if (! is_string($value)) {
            return false;
        }

        $normalized = str($value)
            ->replace('&nbsp;', ' ')
            ->stripTags()
            ->trim()
            ->toString();

        return $normalized === '';
    }

    public function translationAttribute(string $field): string
    {
        return str_ends_with($field, '_translations')
            ? $field
            : $field.'_translations';
    }

    public function baseField(string $field): string
    {
        return str_ends_with($field, '_translations')
            ? substr($field, 0, -13)
            : $field;
    }

    /**
     * @param  array<string, mixed>  $values
     * @param  array<string, mixed>  $copyFlags
     * @return array<string, mixed>
     */
    private function normalizeField(array $values, array $copyFlags): array
    {
        $defaultLocale = $this->locales->defaultLocale();
        $defaultValue = $values[$defaultLocale] ?? null;
        $normalized = [];

        foreach ($this->locales->activeCodes() as $code) {
            $value = $values[$code] ?? null;

            if ($code !== $defaultLocale && $this->shouldCopyDefault($copyFlags[$code] ?? false)) {
                $value = $defaultValue;
            }

            $normalized[$code] = is_string($value) && trim($value) === ''
                ? null
                : $value;
        }

        return $normalized;
    }

    private function shouldCopyDefault(mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }
}
