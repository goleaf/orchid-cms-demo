<?php

namespace App\Models;

use Database\Factories\UserDashboardPreferenceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserDashboardPreference extends Model
{
    /** @use HasFactory<UserDashboardPreferenceFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'dashboard',
        'visible_widget_codes',
        'widget_order',
        'filters',
        'refresh_interval_seconds',
        'timezone',
        'is_default',
        'settings',
    ];

    protected $casts = [
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
}
