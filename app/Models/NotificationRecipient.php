<?php

namespace App\Models;

use App\Enums\NotificationRecipientStatus;
use Database\Factories\NotificationRecipientFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NotificationRecipient extends Model
{
    /** @use HasFactory<NotificationRecipientFactory> */
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_QUEUED = 'queued';

    public const STATUS_SENT = 'sent';

    public const STATUS_DELIVERED = 'delivered';

    public const STATUS_FAILED = 'failed';

    public const STATUS_SKIPPED = 'skipped';

    protected $fillable = [
        'message_id',
        'user_id',
        'student_id',
        'lead_id',
        'email',
        'phone',
        'locale',
        'status',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function message(): BelongsTo
    {
        return $this->belongsTo(NotificationMessage::class, 'message_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function studentProfile(): BelongsTo
    {
        return $this->belongsTo(StudentProfile::class, 'student_id');
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class, 'lead_id');
    }

    public function marketingLead(): BelongsTo
    {
        return $this->belongsTo(MarketingLead::class, 'lead_id');
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(NotificationDelivery::class, 'recipient_id');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(NotificationActivity::class, 'recipient_id');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->whereIn('status', [self::STATUS_PENDING, self::STATUS_QUEUED]);
    }

    public function scopeForStudent(Builder $query, Student|StudentProfile|int $student): Builder
    {
        $studentId = $student instanceof StudentProfile ? $student->getKey() : $student;

        return $query->where('student_id', $studentId);
    }

    public function scopeForLead(Builder $query, Lead|MarketingLead|int $lead): Builder
    {
        $leadId = $lead instanceof MarketingLead ? $lead->getKey() : $lead;

        return $query->where('lead_id', $leadId);
    }

    /**
     * @return array<int, string>
     */
    public static function statusValues(): array
    {
        return NotificationRecipientStatus::values();
    }
}
