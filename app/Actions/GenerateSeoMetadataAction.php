<?php

namespace App\Actions;

use App\Services\LocaleManager;
use App\Services\TranslatableContentManager;
use Illuminate\Support\Str;

class GenerateSeoMetadataAction
{
    /**
     * @param  array<string, mixed>  $attributes
     * @param  array<int, string>  $titleFields
     * @param  array<int, string>  $descriptionFields
     * @return array<string, mixed>
     */
    public function handle(
        array $attributes,
        array $titleFields = ['title', 'name'],
        array $descriptionFields = ['excerpt', 'short_description', 'description', 'content'],
    ): array {
        $locales = app(LocaleManager::class)->activeCodes();

        foreach ($locales as $locale) {
            $seoTitle = $this->translation($attributes, 'seo_title', $locale);
            $seoDescription = $this->translation($attributes, 'seo_description', $locale);

            if (blank($seoTitle)) {
                $attributes['seo_title_translations'][$locale] = $this->limited(
                    $this->firstTranslation($attributes, $titleFields, $locale),
                    70,
                );
            }

            if (blank($seoDescription)) {
                $attributes['seo_description_translations'][$locale] = $this->limited(
                    $this->firstTranslation($attributes, $descriptionFields, $locale),
                    160,
                );
            }

            if (blank($this->translation($attributes, 'og_title', $locale))) {
                $attributes['og_title_translations'][$locale] = $this->translation($attributes, 'seo_title', $locale);
            }

            if (blank($this->translation($attributes, 'og_description', $locale))) {
                $attributes['og_description_translations'][$locale] = $this->translation($attributes, 'seo_description', $locale);
            }
        }

        return $attributes;
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @param  array<int, string>  $fields
     */
    private function firstTranslation(array $attributes, array $fields, string $locale): ?string
    {
        foreach ($fields as $field) {
            $value = $this->translation($attributes, $field, $locale);

            if (filled($value)) {
                return $value;
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function translation(array $attributes, string $field, string $locale): ?string
    {
        $attribute = app(TranslatableContentManager::class)->translationAttribute($field);
        $translations = $attributes[$attribute] ?? null;

        if (is_array($translations) && is_scalar($translations[$locale] ?? null)) {
            return $this->clean((string) $translations[$locale]);
        }

        if (is_scalar($attributes[$field] ?? null)) {
            return $this->clean((string) $attributes[$field]);
        }

        return null;
    }

    private function limited(?string $value, int $limit): ?string
    {
        return filled($value) ? Str::limit((string) $value, $limit, '') : null;
    }

    private function clean(?string $value): ?string
    {
        if (! filled($value)) {
            return null;
        }

        $cleaned = trim(strip_tags(str_replace('&nbsp;', ' ', (string) $value)));

        return $cleaned === '' ? null : $cleaned;
    }
}
