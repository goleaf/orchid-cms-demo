<?php

namespace App\Models;

use Database\Factories\UserNotificationPreferenceFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserNotificationPreference extends Model
{
    /** @use HasFactory<UserNotificationPreferenceFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'notification_channel_id',
        'event',
        'is_enabled',
        'quiet_hours_start',
        'quiet_hours_end',
        'send_reminder_before_minutes',
        'settings',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'send_reminder_before_minutes' => 'integer',
        'settings' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function notificationChannel(): BelongsTo
    {
        return $this->belongsTo(NotificationChannel::class);
    }

    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('is_enabled', true);
    }

    public function scopeForEvent(Builder $query, string $event): Builder
    {
        return $query->whereIn('event', ['all', $event]);
    }
}
