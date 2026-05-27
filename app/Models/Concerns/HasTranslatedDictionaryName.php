<?php

namespace App\Models\Concerns;

use App\Models\Language;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;

trait HasTranslatedDictionaryName
{
    use HasTranslations;

    protected static function bootHasTranslatedDictionaryName(): void
    {
        static::saved(fn (): bool => static::flushDictionaryCache());
        static::deleted(fn (): bool => static::flushDictionaryCache());
    }

    public function displayName(?string $locale = null): string
    {
        $locale ??= App::getLocale();

        return $this->getTranslation('name', $locale)
            ?: (string) ($this->name ?: $this->readableDictionaryFallback($this->dictionaryKeyValue()));
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query
            ->orderBy('sort_order')
            ->orderBy($this->dictionaryKeyColumn());
    }

    public function scopeForDictionaryList(Builder $query): Builder
    {
        return $query->select([
            'id',
            $this->dictionaryKeyColumn(),
            'name',
            'name_translations',
            'is_system',
            'is_active',
            'sort_order',
            'created_at',
            'updated_at',
        ]);
    }

    /**
     * @param  array<int, string>|null  $values
     * @return array<string, string>
     */
    public static function translatedLabels(?array $values = null, ?string $locale = null): array
    {
        $locale ??= App::getLocale();
        $labels = Cache::rememberForever(static::dictionaryCacheKey($locale), function () use ($locale): array {
            $prototype = new static;
            $keyColumn = $prototype->dictionaryKeyColumn();

            return static::query()
                ->active()
                ->ordered()
                ->get([
                    'id',
                    $keyColumn,
                    'name',
                    'name_translations',
                ])
                ->mapWithKeys(fn (self $record): array => [
                    (string) $record->getAttribute($keyColumn) => $record->displayName($locale),
                ])
                ->all();
        });

        if ($values === null) {
            return $labels;
        }

        return collect($values)
            ->filter(fn (?string $value): bool => filled($value))
            ->mapWithKeys(fn (string $value): array => [
                $value => $labels[$value] ?? static::readableDictionaryFallback($value),
            ])
            ->all();
    }

    public static function translatedLabel(?string $value, ?string $locale = null): string
    {
        if (! filled($value)) {
            return '-';
        }

        return static::translatedLabels([(string) $value], $locale)[(string) $value]
            ?? static::readableDictionaryFallback((string) $value);
    }

    public static function flushDictionaryCache(): bool
    {
        foreach (Language::activeCodes() as $code) {
            Cache::forget(static::dictionaryCacheKey($code));
        }

        Cache::forget(static::dictionaryCacheKey((string) config('app.locale', 'ru')));
        Cache::forget(static::dictionaryCacheKey((string) config('app.fallback_locale', 'ru')));

        return true;
    }

    public function dictionaryKeyColumn(): string
    {
        return defined(static::class.'::DICTIONARY_KEY_COLUMN')
            ? constant(static::class.'::DICTIONARY_KEY_COLUMN')
            : 'code';
    }

    public function dictionaryKeyValue(): string
    {
        return (string) $this->getAttribute($this->dictionaryKeyColumn());
    }

    protected static function dictionaryCacheKey(string $locale): string
    {
        return 'crm_dictionary.'.static::class.'.'.$locale;
    }

    protected static function readableDictionaryFallback(string $value): string
    {
        return str($value)->replace(['_', '-'], ' ')->title()->toString();
    }
}
