<?php

namespace App\Models;

use App\Enums\AnalyticsReportType;
use App\Enums\ReportGroup;
use App\Models\Concerns\HasTranslations;
use Database\Factories\ReportDefinitionFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ReportDefinition extends Model
{
    /** @use HasFactory<ReportDefinitionFactory> */
    use HasFactory;

    use HasTranslations;
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'code',
        'name_translations',
        'description_translations',
        'report_group',
        'data_source',
        'filters_schema',
        'columns_schema',
        'permissions',
        'report_type',
        'source_model',
        'default_filters',
        'column_config',
        'schedule',
        'is_system',
        'is_active',
        'sort_order',
        'created_by_id',
        'updated_by_id',
    ];

    protected $casts = [
        'name_translations' => 'array',
        'description_translations' => 'array',
        'report_group' => ReportGroup::class,
        'filters_schema' => 'array',
        'columns_schema' => 'array',
        'permissions' => 'array',
        'report_type' => AnalyticsReportType::class,
        'default_filters' => 'array',
        'column_config' => 'array',
        'schedule' => 'array',
        'is_system' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'deleted_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $definition): void {
            if (blank($definition->uuid)) {
                $definition->uuid = (string) Str::uuid();
            }
        });
    }

    public function runs(): HasMany
    {
        return $this->hasMany(ReportRun::class);
    }

    public function exports(): HasMany
    {
        return $this->hasMany(ReportExport::class);
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

    public function dataSource(): ?string
    {
        return $this->data_source ?: $this->source_model;
    }

    /**
     * @return array<int, string>
     */
    public function requiredPermissions(): array
    {
        return array_values(array_filter($this->permissions ?? []));
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForGroup(Builder $query, ReportGroup|string $group): Builder
    {
        $value = $group instanceof ReportGroup ? $group->value : $group;

        return $query->where('report_group', $value);
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
