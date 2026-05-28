<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Database\Factories\PermissionGroupFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PermissionGroup extends Model
{
    /** @use HasFactory<PermissionGroupFactory> */
    use HasFactory;

    use HasTranslations;

    public const DEFAULT_CODES = [
        'website',
        'customer_relationship_management',
        'students',
        'education',
        'schedule',
        'lessons',
        'driving',
        'documents',
        'finance',
        'exams',
        'notifications',
        'analytics',
        'security',
        'system',
    ];

    protected $fillable = [
        'code',
        'name_translations',
        'description_translations',
        'icon',
        'color',
        'sort_order',
        'is_active',
        'is_system',
    ];

    protected $casts = [
        'name_translations' => 'array',
        'description_translations' => 'array',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
        'is_system' => 'boolean',
    ];

    public function permissions(): HasMany
    {
        return $this->hasMany(PermissionRegistryItem::class, 'permission_group_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeSystem(Builder $query): Builder
    {
        return $query->where('is_system', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query
            ->orderBy('sort_order')
            ->orderBy('code');
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->displayName();
    }

    public function getDisplayDescriptionAttribute(): string
    {
        return $this->displayDescription();
    }

    public function displayName(?string $locale = null): string
    {
        $translated = $this->getTranslation('name', $locale);

        if (filled($translated)) {
            return (string) $translated;
        }

        $key = 'security.permission_groups.'.$this->code;
        $label = tkey($key);

        return $label !== $key
            ? $label
            : str((string) $this->code)->replace(['_', '-'], ' ')->title()->toString();
    }

    public function displayDescription(?string $locale = null): string
    {
        return (string) ($this->getTranslation('description', $locale) ?: '');
    }
}
