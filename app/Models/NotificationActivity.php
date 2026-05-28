<?php

namespace App\Models;

use Database\Factories\NotificationActivityFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationActivity extends Model
{
    /** @use HasFactory<NotificationActivityFactory> */
    use HasFactory;

    public const TYPE_CREATED = 'created';

    public const TYPE_SENT = 'sent';

    public const TYPE_DELIVERED = 'delivered';

    public const TYPE_FAILED = 'failed';

    public const TYPE_READ = 'read';

    protected $fillable = [
        'message_id',
        'recipient_id',
        'delivery_id',
        'user_id',
        'student_id',
        'lead_id',
        'activity_type',
        'description',
        'occurred_at',
        'metadata',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function message(): BelongsTo
    {
        return $this->belongsTo(NotificationMessage::class, 'message_id');
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(NotificationRecipient::class, 'recipient_id');
    }

    public function delivery(): BelongsTo
    {
        return $this->belongsTo(NotificationDelivery::class, 'delivery_id');
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

    public function scopeForType(Builder $query, string $type): Builder
    {
        return $query->where('activity_type', $type);
    }
}
