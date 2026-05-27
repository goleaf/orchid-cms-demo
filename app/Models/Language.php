<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Language extends Model
{
    public const CACHE_KEY_DEFAULT = 'languages.default';

    public const CACHE_KEY_ACTIVE = 'languages.active';

    protected $fillable = [
        'code',
        'name',
        'native_name',
        'is_default',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(function (Language $language): void {
            if ($language->is_default) {
                $language->is_active = true;
            }
        });

        static::saved(function (Language $language): void {
            if ($language->is_default) {
                static::query()
                    ->whereKeyNot($language->getKey())
                    ->where('is_default', true)
                    ->update(['is_default' => false]);
            }

            static::flushLanguageCache();
        });

        static::deleted(fn (): bool => static::flushLanguageCache());
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query
            ->select([
                'id',
                'code',
                'name',
                'native_name',
                'is_default',
                'is_active',
                'sort_order',
            ])
            ->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query
            ->orderBy('sort_order')
            ->orderBy('name');
    }

    public static function defaultCode(): string
    {
        return Cache::rememberForever(static::CACHE_KEY_DEFAULT, function (): string {
            return (string) (static::query()
                ->where('is_default', true)
                ->value('code') ?: config('app.locale', 'ru'));
        });
    }

    /**
     * @return array<int, string>
     */
    public static function activeCodes(): array
    {
        return Cache::rememberForever(static::CACHE_KEY_ACTIVE, function (): array {
            return static::active()
                ->ordered()
                ->pluck('code')
                ->all();
        });
    }

    public static function flushLanguageCache(): bool
    {
        Cache::forget(static::CACHE_KEY_DEFAULT);
        Cache::forget(static::CACHE_KEY_ACTIVE);

        return true;
    }
}
