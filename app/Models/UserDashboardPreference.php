<?php

namespace App\Models;

use Database\Factories\UserDashboardPreferenceFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserDashboardPreference extends Model
{
    /** @use HasFactory<UserDashboardPreferenceFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'analytics_dashboard_id',
        'dashboard',
        'layout',
        'visible_widget_codes',
        'widget_order',
        'filters',
        'refresh_interval_seconds',
        'timezone',
        'is_default',
        'settings',
    ];

    protected $casts = [
        'layout' => 'array',
        'visible_widget_codes' => 'array',
        'widget_order' => 'array',
        'filters' => 'array',
        'refresh_interval_seconds' => 'integer',
        'is_default' => 'boolean',
        'settings' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function analyticsDashboard(): BelongsTo
    {
        return $this->belongsTo(AnalyticsDashboard::class, 'analytics_dashboard_id');
    }

    public function scopeDefault(Builder $query): Builder
    {
        return $query->where('is_default', true);
    }

    public function scopeForDashboard(Builder $query, AnalyticsDashboard|int|string $dashboard): Builder
    {
        if ($dashboard instanceof AnalyticsDashboard) {
            return $query->where('analytics_dashboard_id', $dashboard->getKey());
        }

        if (is_int($dashboard)) {
            return $query->where('analytics_dashboard_id', $dashboard);
        }

        return $query->where('dashboard', $dashboard);
    }
}
