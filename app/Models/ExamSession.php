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
use Illuminate\Support\Str;

class ExamSession extends Model
{
    /** @use HasFactory<ExamSessionFactory> */
    use HasFactory;

    protected $fillable = [
        'uuid',
        'branch_id',
        'training_program_id',
        'training_group_id',
        'instructor_id',
        'vehicle_id',
        'exam_type',
        'provider',
        'status',
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
        });
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

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(Instructor::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(ExamAttempt::class);
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

    public function scopeForExamList(Builder $query): Builder
    {
        return $query->select([
            'id',
            'uuid',
            'branch_id',
            'training_program_id',
            'training_group_id',
            'instructor_id',
            'vehicle_id',
            'exam_type',
            'provider',
            'status',
            'starts_at',
            'ends_at',
            'location',
            'capacity',
            'seats_taken',
            'external_reference',
        ]);
    }
}
