<?php

namespace App\Models;

use Database\Factories\ReminderScheduleFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ReminderSchedule extends Model
{
    /** @use HasFactory<ReminderScheduleFactory> */
    use HasFactory;

    public const STATUS_SCHEDULED = 'scheduled';

    public const STATUS_QUEUED = 'queued';

    public const STATUS_SENT = 'sent';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'rule_id',
        'target_type',
        'target_id',
        'message_id',
        'scheduled_at',
        'status',
        'processed_at',
        'metadata',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'processed_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function rule(): BelongsTo
    {
        return $this->belongsTo(ReminderRule::class, 'rule_id');
    }

    public function message(): BelongsTo
    {
        return $this->belongsTo(NotificationMessage::class, 'message_id');
    }

    public function target(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'target_type', 'target_id');
    }

    public function scopeScheduled(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_SCHEDULED);
    }

    public function scopeDue(Builder $query): Builder
    {
        return $query->scheduled()->where('scheduled_at', '<=', now());
    }

    /**
     * @return array<int, string>
     */
    public static function statusValues(): array
    {
        return [
            self::STATUS_SCHEDULED,
            self::STATUS_QUEUED,
            self::STATUS_SENT,
            self::STATUS_CANCELLED,
            self::STATUS_FAILED,
        ];
    }
}
