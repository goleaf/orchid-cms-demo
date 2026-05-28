<?php

namespace App\Models;

use App\Enums\AnalyticsReportType;
use App\Models\Concerns\HasTranslations;
use Database\Factories\ReportDefinitionFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReportDefinition extends Model
{
    /** @use HasFactory<ReportDefinitionFactory> */
    use HasFactory;
    use HasTranslations;

    protected $fillable = [
        'code',
        'name_translations',
        'description_translations',
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
        'report_type' => AnalyticsReportType::class,
        'default_filters' => 'array',
        'column_config' => 'array',
        'schedule' => 'array',
        'is_system' => 'boolean',
        'is_active' => 'boolean',
    ];

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

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('code');
    }
}
