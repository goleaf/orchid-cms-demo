<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Database\Factories\PermissionRegistryItemFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PermissionRegistryItem extends Model
{
    /** @use HasFactory<PermissionRegistryItemFactory> */
    use HasFactory;

    use HasTranslations;

    public const RISK_LOW = 'low';

    public const RISK_NORMAL = 'normal';

    public const RISK_HIGH = 'high';

    public const RISK_CRITICAL = 'critical';

    public const RISK_LEVELS = [
        self::RISK_LOW,
        self::RISK_NORMAL,
        self::RISK_HIGH,
        self::RISK_CRITICAL,
    ];

    public const MODULES = [
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
        'permission_group_id',
        'code',
        'name_translations',
        'description_translations',
        'module',
        'risk_level',
        'is_active',
        'is_system',
        'sort_order',
    ];

    protected $casts = [
        'name_translations' => 'array',
        'description_translations' => 'array',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
        'is_system' => 'boolean',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(PermissionGroup::class, 'permission_group_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeSystem(Builder $query): Builder
    {
        return $query->where('is_system', true);
    }

    public function scopeByModule(Builder $query, ?string $module): Builder
    {
        return filled($module) ? $query->where('module', $module) : $query;
    }

    public function scopeByRiskLevel(Builder $query, ?string $riskLevel): Builder
    {
        return filled($riskLevel) ? $query->where('risk_level', $riskLevel) : $query;
    }

    public function scopeByGroup(Builder $query, PermissionGroup|int|string|null $group): Builder
    {
        if ($group === null || $group === '') {
            return $query;
        }

        if ($group instanceof PermissionGroup) {
            return $query->where('permission_group_id', $group->getKey());
        }

        if (is_numeric($group)) {
            return $query->where('permission_group_id', (int) $group);
        }

        return $query->whereHas('group', fn (Builder $query): Builder => $query->where('code', (string) $group));
    }

    public function scopeCritical(Builder $query): Builder
    {
        return $query->where('risk_level', self::RISK_CRITICAL);
    }

    public function scopeHighRisk(Builder $query): Builder
    {
        return $query->whereIn('risk_level', [self::RISK_HIGH, self::RISK_CRITICAL]);
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

    public function getDisplayRiskLevelAttribute(): string
    {
        $key = 'security.risk_levels.'.$this->risk_level;
        $label = tkey($key);

        return $label !== $key
            ? $label
            : str((string) $this->risk_level)->replace(['_', '-'], ' ')->title()->toString();
    }

    public function getIsCriticalAttribute(): bool
    {
        return $this->risk_level === self::RISK_CRITICAL;
    }

    public function getIsHighRiskAttribute(): bool
    {
        return in_array($this->risk_level, [self::RISK_HIGH, self::RISK_CRITICAL], true);
    }

    public function displayName(?string $locale = null): string
    {
        $translated = $this->getTranslation('name', $locale);

        if (filled($translated)) {
            return (string) $translated;
        }

        return str((string) $this->code)->replace(['.', '_', '-'], ' ')->title()->toString();
    }

    public function displayDescription(?string $locale = null): string
    {
        return (string) ($this->getTranslation('description', $locale) ?: '');
    }
}
