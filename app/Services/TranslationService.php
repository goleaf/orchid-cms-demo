<?php

namespace App\Services;

use App\Models\Language;
use App\Models\TranslationValue;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class TranslationService
{
    public function get(string $key, array $replace = [], ?string $locale = null): string
    {
        $locale ??= App::getLocale();

        $value = $this->databaseValue($key, $locale);

        if ($value === null) {
            $fallbackLocale = $this->defaultLocale();

            if ($fallbackLocale !== $locale) {
                $value = $this->databaseValue($key, $fallbackLocale);
            }
        }

        if ($value !== null) {
            return $this->replace($value, $replace);
        }

        if (Lang::has($key, $locale)) {
            return __($key, $replace, $locale);
        }

        return $key;
    }

    private function databaseValue(string $key, string $locale): ?string
    {
        try {
            if (! Schema::hasTable('translation_values') || ! Schema::hasTable('translation_strings')) {
                return null;
            }
        } catch (QueryException) {
            return null;
        }

        $translations = Cache::rememberForever(
            TranslationValue::cacheKey($locale),
            fn (): array => TranslationValue::query()
                ->approvedForLocale($locale)
                ->with('translationString:id,key')
                ->get()
                ->mapWithKeys(fn (TranslationValue $value): array => [
                    $value->translationString?->key => $value->value,
                ])
                ->filter(fn (mixed $value, mixed $key): bool => is_string($key) && filled($value))
                ->all(),
        );

        $value = $translations[$key] ?? null;

        return filled($value) ? (string) $value : null;
    }

    private function defaultLocale(): string
    {
        try {
            if (Schema::hasTable('languages')) {
                return Language::defaultCode();
            }
        } catch (QueryException) {
        }

        return config('app.fallback_locale', config('app.locale', 'ru'));
    }

    private function replace(string $value, array $replace): string
    {
        if ($replace === []) {
            return $value;
        }

        $tokens = [];

        foreach ($replace as $key => $replacement) {
            $replacement = (string) $replacement;
            $tokens[':'.$key] = $replacement;
            $tokens[':'.Str::upper((string) $key)] = Str::upper($replacement);
            $tokens[':'.Str::ucfirst((string) $key)] = Str::ucfirst($replacement);
        }

        return strtr($value, $tokens);
    }
}
