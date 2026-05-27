<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class TranslationValue extends Model
{
    public const CACHE_PREFIX = 'translation_values.';

    protected $fillable = [
        'translation_string_id',
        'language_code',
        'value',
        'is_approved',
    ];

    protected $casts = [
        'is_approved' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saved(fn (): bool => static::flushTranslationCache());
        static::deleted(fn (): bool => static::flushTranslationCache());
    }

    public function translationString(): BelongsTo
    {
        return $this->belongsTo(TranslationString::class);
    }

    public function scopeApprovedForLocale(Builder $query, string $locale): Builder
    {
        return $query
            ->select([
                'id',
                'translation_string_id',
                'language_code',
                'value',
                'is_approved',
            ])
            ->where('language_code', $locale)
            ->where('is_approved', true)
            ->whereNotNull('value')
            ->where('value', '<>', '');
    }

    public static function cacheKey(string $locale): string
    {
        return static::CACHE_PREFIX.$locale;
    }

    public static function flushTranslationCache(): bool
    {
        foreach (Language::activeCodes() as $code) {
            Cache::forget(static::cacheKey($code));
        }

        Cache::forget(static::CACHE_PREFIX.config('app.locale', 'ru'));
        Cache::forget(static::CACHE_PREFIX.config('app.fallback_locale', 'ru'));

        return true;
    }
}
