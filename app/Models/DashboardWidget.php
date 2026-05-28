<?php

namespace App\Models;

use App\Enums\DashboardWidgetType;
use App\Models\Concerns\HasTranslations;
use Database\Factories\DashboardWidgetFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class DashboardWidget extends Model
{
    /** @use HasFactory<DashboardWidgetFactory> */
    use HasFactory;

    use HasTranslations;
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'analytics_dashboard_id',
        'code',
        'title_translations',
        'name_translations',
        'description_translations',
        'widget_type',
        'metric_code',
        'component',
        'config',
        'filters',
        'width',
        'height',
        'is_system',
        'is_active',
        'sort_order',
        'settings',
        'created_by_id',
        'updated_by_id',
    ];

    protected $casts = [
        'title_translations' => 'array',
        'name_translations' => 'array',
        'description_translations' => 'array',
        'config' => 'array',
        'filters' => 'array',
        'width' => 'integer',
        'height' => 'integer',
        'is_system' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'settings' => 'array',
        'deleted_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $widget): void {
            if (blank($widget->uuid)) {
                $widget->uuid = (string) Str::uuid();
            }
        });
    }

    public function dashboard(): BelongsTo
    {
        return $this->belongsTo(AnalyticsDashboard::class, 'analytics_dashboard_id');
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
        return $this->displayTitle($locale);
    }

    public function displayTitle(?string $locale = null): string
    {
        return $this->getTranslation('title', $locale)
            ?: $this->getTranslation('name', $locale)
            ?: str($this->code)->replace(['.', '_', '-'], ' ')->title()->toString();
    }

    public function displayDescription(?string $locale = null): ?string
    {
        return $this->getTranslation('description', $locale);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForDashboard(Builder $query, AnalyticsDashboard|int $dashboard): Builder
    {
        $dashboardId = $dashboard instanceof AnalyticsDashboard ? $dashboard->getKey() : $dashboard;

        return $query->where('analytics_dashboard_id', $dashboardId);
    }

    public function scopeOfType(Builder $query, DashboardWidgetType|string $type): Builder
    {
        $value = $type instanceof DashboardWidgetType ? $type->value : $type;

        return $query->where('widget_type', $value);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('code');
    }
}
