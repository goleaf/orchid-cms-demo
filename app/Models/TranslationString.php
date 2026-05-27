<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TranslationString extends Model
{
    protected $fillable = [
        'group',
        'key',
        'description',
        'is_system',
    ];

    protected $casts = [
        'is_system' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saved(fn (): bool => TranslationValue::flushTranslationCache());
        static::deleted(fn (): bool => TranslationValue::flushTranslationCache());
    }

    public function values(): HasMany
    {
        return $this->hasMany(TranslationValue::class);
    }

    public function scopeForManagerList(Builder $query): Builder
    {
        return $query
            ->select([
                'id',
                'group',
                'key',
                'description',
                'is_system',
                'created_at',
                'updated_at',
            ])
            ->with(['values:id,translation_string_id,language_code,value,is_approved'])
            ->withCount([
                'values as missing_values_count' => fn (Builder $query): Builder => $query
                    ->where(fn (Builder $query): Builder => $query
                        ->whereNull('value')
                        ->orWhere('value', '')),
            ]);
    }
}
