<?php

namespace App\Models;

use App\Enums\CommunicationPriority;
use App\Enums\CommunicationReminderStatus;
use App\Models\Concerns\HasTranslations;
use Database\Factories\CommunicationReminderFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

class CommunicationReminder extends Model
{
    /** @use HasFactory<CommunicationReminderFactory> */
    use HasFactory;

    use HasTranslations;

    public const STATUS_SCHEDULED = 'scheduled';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_FAILED = 'failed';

    public const PRIORITY_LOW = 'low';

    public const PRIORITY_NORMAL = 'normal';

    public const PRIORITY_HIGH = 'high';

    public const PRIORITY_URGENT = 'urgent';

    protected $fillable = [
        'uuid',
        'remindable_type',
        'remindable_id',
        'marketing_lead_id',
        'student_profile_id',
        'student_enrollment_id',
        'assigned_to_user_id',
        'notification_channel_id',
        'communication_template_id',
        'status',
        'priority',
        'title_translations',
        'body_translations',
        'note',
        'due_at',
        'completed_at',
        'cancelled_at',
        'last_attempted_at',
        'created_by_id',
        'updated_by_id',
        'completed_by_id',
        'metadata',
    ];

    protected $casts = [
        'title_translations' => 'array',
        'body_translations' => 'array',
        'metadata' => 'array',
        'due_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'last_attempted_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $reminder): void {
            if (blank($reminder->uuid)) {
                $reminder->uuid = (string) Str::uuid();
            }
        });
    }

    public function remindable(): MorphTo
    {
        return $this->morphTo();
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class, 'marketing_lead_id');
    }

    public function marketingLead(): BelongsTo
    {
        return $this->belongsTo(MarketingLead::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_profile_id');
    }

    public function studentProfile(): BelongsTo
    {
        return $this->belongsTo(StudentProfile::class);
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(StudentEnrollment::class, 'student_enrollment_id');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    public function notificationChannel(): BelongsTo
    {
        return $this->belongsTo(NotificationChannel::class);
    }

    public function communicationTemplate(): BelongsTo
    {
        return $this->belongsTo(CommunicationTemplate::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_id');
    }

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by_id');
    }

    public function deliveryLogs(): HasMany
    {
        return $this->hasMany(NotificationDeliveryLog::class);
    }

    public function studentCommunications(): HasMany
    {
        return $this->hasMany(StudentCommunication::class);
    }

    public function scopeScheduled(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_SCHEDULED);
    }

    public function scopeDue(Builder $query): Builder
    {
        return $query->scheduled()->where('due_at', '<=', now());
    }

    public function scopeForList(Builder $query): Builder
    {
        return $query->select([
            'id',
            'uuid',
            'marketing_lead_id',
            'student_profile_id',
            'student_enrollment_id',
            'assigned_to_user_id',
            'notification_channel_id',
            'communication_template_id',
            'status',
            'priority',
            'title_translations',
            'body_translations',
            'note',
            'due_at',
            'completed_at',
            'cancelled_at',
            'created_at',
            'updated_at',
        ]);
    }

    public function displayTitle(?string $locale = null): string
    {
        return $this->getTranslation('title', $locale)
            ?: $this->communicationTemplate?->displayName($locale)
            ?: tkey('communication.reminders.fallback_title');
    }

    public function statusLabel(): string
    {
        return tkey('communication.reminders.statuses.'.$this->status);
    }

    public function priorityLabel(): string
    {
        return tkey('communication.reminders.priorities.'.$this->priority);
    }

    /**
     * @return array<int, string>
     */
    public static function statusValues(): array
    {
        return CommunicationReminderStatus::values();
    }

    /**
     * @return array<int, string>
     */
    public static function priorityValues(): array
    {
        return CommunicationPriority::values();
    }
}
