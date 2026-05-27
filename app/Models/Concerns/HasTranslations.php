<?php

namespace App\Models\Concerns;

use App\Models\Language;
use Illuminate\Support\Facades\App;

trait HasTranslations
{
    public function getTranslation(string $field, ?string $locale = null, ?string $fallbackLocale = null): ?string
    {
        $locale ??= App::getLocale();
        $fallbackLocale ??= Language::defaultCode();

        $translations = $this->getTranslations($field);

        if (filled($translations[$locale] ?? null)) {
            return (string) $translations[$locale];
        }

        if ($fallbackLocale !== $locale && filled($translations[$fallbackLocale] ?? null)) {
            return (string) $translations[$fallbackLocale];
        }

        return null;
    }

    public function setTranslation(string $field, string $locale, ?string $value): void
    {
        $translations = $this->getTranslations($field);
        $translations[$locale] = $value;

        $this->setTranslations($field, $translations);
    }

    /**
     * @return array<string, mixed>
     */
    public function getTranslations(string $field): array
    {
        $attribute = $this->translationAttribute($field);
        $translations = $this->getAttribute($attribute);

        return is_array($translations) ? $translations : [];
    }

    /**
     * @param  array<string, mixed>  $values
     */
    public function setTranslations(string $field, array $values): void
    {
        $this->setAttribute($this->translationAttribute($field), $values);
    }

    protected function translationAttribute(string $field): string
    {
        return str_ends_with($field, '_translations')
            ? $field
            : $field.'_translations';
    }
}
