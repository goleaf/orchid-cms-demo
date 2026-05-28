<?php

namespace App\Models;

use App\Enums\KpiMetricGroup;
use App\Models\Concerns\HasTranslations;
use Database\Factories\KpiMetricFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class KpiMetric extends Model
{
    /** @use HasFactory<KpiMetricFactory> */
    use HasFactory;

    use HasTranslations;
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'code',
        'name_translations',
        'description_translations',
        'metric_group',
        'category',
        'value_type',
        'unit',
        'calculation_type',
        'calculation',
        'source',
        'is_system',
        'is_active',
        'sort_order',
        'settings',
        'created_by_id',
        'updated_by_id',
    ];

    protected $casts = [
        'name_translations' => 'array',
        'description_translations' => 'array',
        'metric_group' => KpiMetricGroup::class,
        'is_system' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'settings' => 'array',
        'deleted_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $metric): void {
            if (blank($metric->uuid)) {
                $metric->uuid = (string) Str::uuid();
            }
        });
    }

    public function targets(): HasMany
    {
        return $this->hasMany(KpiTarget::class);
    }

    public function snapshots(): HasMany
    {
        return $this->hasMany(KpiSnapshot::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_id');
    }

    public function displayName(?string $locale = null): string
    {
        return $this->getTranslation('name', $locale)
            ?: str($this->code)->replace(['.', '_', '-'], ' ')->title()->toString();
    }

    public function displayDescription(?string $locale = null): ?string
    {
        return $this->getTranslation('description', $locale);
    }

    public function groupValue(): string
    {
        return $this->metric_group?->value ?: (string) $this->category;
    }

    public function calculationType(): ?string
    {
        return $this->calculation_type ?: $this->calculation;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForGroup(Builder $query, KpiMetricGroup|string $group): Builder
    {
        $value = $group instanceof KpiMetricGroup ? $group->value : $group;

        return $query->where('metric_group', $value);
    }

    public function scopeSystem(Builder $query): Builder
    {
        return $query->where('is_system', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('code');
    }
}
