<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Database\Factories\UserStatusFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserStatus extends Model
{
    /** @use HasFactory<UserStatusFactory> */
    use HasFactory;

    use HasTranslations;

    protected $fillable = [
        'code',
        'name_translations',
        'description_translations',
        'color',
        'sort_order',
        'is_default',
        'is_active',
        'is_blocked',
        'is_archived',
        'is_final',
    ];

    protected $casts = [
        'name_translations' => 'array',
        'description_translations' => 'array',
        'sort_order' => 'integer',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'is_blocked' => 'boolean',
        'is_archived' => 'boolean',
        'is_final' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $status): void {
            if ($status->is_default) {
                $status->is_active = true;
            }
        });

        static::saved(function (self $status): void {
            if (! $status->is_default) {
                return;
            }

            static::query()
                ->whereKeyNot($status->getKey())
                ->where('is_default', true)
                ->update(['is_default' => false]);
        });
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'status_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeDefault(Builder $query): Builder
    {
        return $query->where('is_default', true);
    }

    public function scopeBlocked(Builder $query): Builder
    {
        return $query->where('is_blocked', true);
    }

    public function scopeArchived(Builder $query): Builder
    {
        return $query->where('is_archived', true);
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->displayName();
    }

    public function displayName(?string $locale = null): string
    {
        $translated = $this->getTranslation('name', $locale);

        if (filled($translated)) {
            return (string) $translated;
        }

        $key = 'security.user_statuses.'.$this->code;
        $label = tkey($key);

        return $label !== $key
            ? $label
            : str((string) $this->code)->replace(['_', '-'], ' ')->title()->toString();
    }
}
