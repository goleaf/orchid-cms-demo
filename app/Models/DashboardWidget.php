<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Database\Factories\DashboardWidgetFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DashboardWidget extends Model
{
    /** @use HasFactory<DashboardWidgetFactory> */
    use HasFactory;
    use HasTranslations;

    protected $fillable = [
        'code',
        'name_translations',
        'description_translations',
        'widget_type',
        'metric_code',
        'component',
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
        'is_system' => 'boolean',
        'is_active' => 'boolean',
        'settings' => 'array',
    ];

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
