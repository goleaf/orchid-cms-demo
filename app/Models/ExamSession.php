<?php

namespace App\Models;

use App\Enums\ExamSessionStatus;
use App\Enums\ExamType;
use Database\Factories\ExamSessionFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Str;

class ExamSession extends Model
{
    /** @use HasFactory<ExamSessionFactory> */
    use HasFactory;

    protected $fillable = [
        'uuid',
        'exam_number',
        'type_id',
        'status_id',
        'branch_id',
        'group_id',
        'training_program_id',
        'training_group_id',
        'instructor_id',
        'vehicle_id',
        'classroom_id',
        'exam_type',
        'provider',
        'status',
        'scheduled_at',
        'examiner_id',
        'starts_at',
        'ends_at',
        'location',
        'capacity',
        'seats_taken',
        'external_reference',
        'official_placeholder_payload',
        'notes',
        'internal_notes',
        'created_by_id',
        'updated_by_id',
    ];

    protected $casts = [
        'exam_type' => ExamType::class,
        'status' => ExamSessionStatus::class,
        'scheduled_at' => 'datetime',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'capacity' => 'integer',
        'seats_taken' => 'integer',
        'official_placeholder_payload' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $session): void {
            if (blank($session->uuid)) {
                $session->uuid = (string) Str::uuid();
            }

            if (blank($session->exam_number)) {
                $session->exam_number = 'EXM-'.now()->format('Ymd').'-'.Str::upper(Str::random(8));
            }

            if ($session->scheduled_at === null && $session->starts_at !== null) {
                $session->scheduled_at = $session->starts_at;
            }

            if ($session->starts_at === null && $session->scheduled_at !== null) {
                $session->starts_at = $session->scheduled_at;
            }

            if ($session->group_id === null && $session->training_group_id !== null) {
                $session->group_id = $session->training_group_id;
            }

            if ($session->training_group_id === null && $session->group_id !== null) {
                $session->training_group_id = $session->group_id;
            }
        });
    }

    public function typeRecord(): BelongsTo
    {
        return $this->belongsTo(\App\Models\ExamType::class, 'type_id');
    }

    public function statusRecord(): BelongsTo
    {
        return $this->belongsTo(ExamStatus::class, 'status_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function trainingProgram(): BelongsTo
    {
        return $this->belongsTo(TrainingProgram::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(TrainingGroup::class, 'training_group_id');
    }

    public function groupAlias(): BelongsTo
    {
        return $this->belongsTo(TrainingGroup::class, 'group_id');
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(Instructor::class);
    }

    public function examiner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'examiner_id');
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(ExamAttempt::class);
    }

    public function participants(): HasMany
    {
        return $this->hasMany(ExamParticipant::class);
    }

    public function checklistItems(): HasMany
    {
        return $this->hasMany(ExamChecklistItem::class);
    }

    public function results(): HasManyThrough
    {
        return $this->hasManyThrough(ExamResult::class, ExamAttempt::class, 'exam_session_id', 'attempt_id');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(ExamActivity::class);
    }

    public function availableSeats(): int
    {
        return max(0, $this->capacity - $this->seats_taken);
    }

    public function acceptsAttempt(): bool
    {
        return $this->status->acceptsAttempts()
            && $this->availableSeats() > 0;
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query
            ->whereIn('status', [
                ExamSessionStatus::Planned->value,
                ExamSessionStatus::Open->value,
            ])
            ->where('starts_at', '>=', now())
            ->orderBy('starts_at');
    }

    public function scopeScheduledAfter(Builder $query, mixed $date): Builder
    {
        return $query->where('scheduled_at', '>=', $date);
    }

    public function scopeForType(Builder $query, \App\Models\ExamType|int|string|null $type): Builder
    {
        if ($type instanceof \App\Models\ExamType) {
            return $query->where('type_id', $type->getKey());
        }

        return filled($type)
            ? $query->where('type_id', $type)
            : $query;
    }

    public function scopeForExamList(Builder $query): Builder
    {
        return $query->select([
            'id',
            'uuid',
            'exam_number',
            'type_id',
            'status_id',
            'branch_id',
            'group_id',
            'training_program_id',
            'training_group_id',
            'instructor_id',
            'vehicle_id',
            'classroom_id',
            'exam_type',
            'provider',
            'status',
            'scheduled_at',
            'examiner_id',
            'starts_at',
            'ends_at',
            'location',
            'capacity',
            'seats_taken',
            'external_reference',
        ]);
    }
}
