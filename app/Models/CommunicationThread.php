<?php

namespace App\Models;

use App\Enums\CommunicationThreadStatus;
use Database\Factories\CommunicationThreadFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

class CommunicationThread extends Model
{
    /** @use HasFactory<CommunicationThreadFactory> */
    use HasFactory;

    public const STATUS_OPEN = 'open';

    public const STATUS_CLOSED = 'closed';

    public const STATUS_ARCHIVED = 'archived';

    protected $fillable = [
        'thread_number',
        'subject',
        'target_type',
        'target_id',
        'student_id',
        'lead_id',
        'status',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $thread): void {
            if (blank($thread->thread_number)) {
                $thread->thread_number = 'THR-'.now()->format('Ymd').'-'.Str::upper(Str::random(8));
            }
        });
    }

    public function target(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'target_type', 'target_id');
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

    public function messages(): HasMany
    {
        return $this->hasMany(CommunicationMessage::class, 'thread_id');
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_OPEN);
    }

    /**
     * @return array<int, string>
     */
    public static function statusValues(): array
    {
        return CommunicationThreadStatus::values();
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
}
