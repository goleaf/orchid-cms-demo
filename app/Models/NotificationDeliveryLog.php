<?php

namespace App\Models;

use App\Enums\CommunicationDirection;
use App\Enums\NotificationDeliveryLogStatus;
use Database\Factories\NotificationDeliveryLogFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

class NotificationDeliveryLog extends Model
{
    /** @use HasFactory<NotificationDeliveryLogFactory> */
    use HasFactory;

    public const STATUS_QUEUED = 'queued';

    public const STATUS_SENT = 'sent';

    public const STATUS_FAILED = 'failed';

    public const STATUS_SKIPPED = 'skipped';

    public const STATUS_READ = 'read';

    public const DIRECTION_INBOUND = 'inbound';

    public const DIRECTION_OUTBOUND = 'outbound';

    public const DIRECTION_INTERNAL = 'internal';

    protected $fillable = [
        'uuid',
        'notifiable_type',
        'notifiable_id',
        'user_id',
        'student_profile_id',
        'marketing_lead_id',
        'student_communication_id',
        'notification_channel_id',
        'communication_template_id',
        'communication_reminder_id',
        'database_notification_id',
        'direction',
        'status',
        'recipient_name',
        'recipient_email',
        'recipient_phone',
        'recipient_external_id',
        'subject',
        'body',
        'provider',
        'provider_message_id',
        'provider_status',
        'error_message',
        'queued_at',
        'scheduled_at',
        'sent_at',
        'failed_at',
        'read_at',
        'created_by_id',
        'metadata',
    ];

    protected $casts = [
        'queued_at' => 'datetime',
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
        'failed_at' => 'datetime',
        'read_at' => 'datetime',
        'metadata' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $log): void {
            if (blank($log->uuid)) {
                $log->uuid = (string) Str::uuid();
            }
        });
    }

    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_profile_id');
    }

    public function studentProfile(): BelongsTo
    {
        return $this->belongsTo(StudentProfile::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class, 'marketing_lead_id');
    }

    public function studentCommunication(): BelongsTo
    {
        return $this->belongsTo(StudentCommunication::class);
    }

    public function notificationChannel(): BelongsTo
    {
        return $this->belongsTo(NotificationChannel::class);
    }

    public function communicationTemplate(): BelongsTo
    {
        return $this->belongsTo(CommunicationTemplate::class);
    }

    public function communicationReminder(): BelongsTo
    {
        return $this->belongsTo(CommunicationReminder::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function scopeForList(Builder $query): Builder
    {
        return $query->select([
            'id',
            'uuid',
            'user_id',
            'student_profile_id',
            'marketing_lead_id',
            'student_communication_id',
            'notification_channel_id',
            'communication_template_id',
            'communication_reminder_id',
            'direction',
            'status',
            'recipient_name',
            'recipient_email',
            'recipient_phone',
            'subject',
            'provider',
            'provider_status',
            'queued_at',
            'scheduled_at',
            'sent_at',
            'failed_at',
            'created_at',
            'updated_at',
        ]);
    }

    public function statusLabel(): string
    {
        return tkey('communication.delivery_logs.statuses.'.$this->status);
    }

    /**
     * @return array<int, string>
     */
    public static function statusValues(): array
    {
        return NotificationDeliveryLogStatus::values();
    }

    /**
     * @return array<int, string>
     */
    public static function directionValues(): array
    {
        return CommunicationDirection::values();
    }
}
