<?php

namespace App\Models;

use App\Enums\GroupStatus;
use Database\Factories\TrainingGroupFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrainingGroup extends Model
{
    /** @use HasFactory<TrainingGroupFactory> */
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'training_program_id',
        'instructor_id',
        'name',
        'code',
        'status',
        'capacity',
        'starts_on',
        'ends_on',
        'meeting_days',
        'meeting_time',
        'classroom',
    ];

    protected $casts = [
        'status' => GroupStatus::class,
        'capacity' => 'integer',
        'starts_on' => 'date',
        'ends_on' => 'date',
        'meeting_days' => 'array',
        'meeting_time' => 'datetime:H:i',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function trainingProgram(): BelongsTo
    {
        return $this->belongsTo(TrainingProgram::class);
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(Instructor::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function seatsAvailable(): int
    {
        return max(0, $this->capacity - (int) ($this->enrollments_count ?? 0));
    }

    public function scopeOperationalList(Builder $query): Builder
    {
        return $query->select([
            'id',
            'branch_id',
            'training_program_id',
            'instructor_id',
            'name',
            'code',
            'status',
            'capacity',
            'starts_on',
            'ends_on',
            'meeting_days',
            'meeting_time',
            'classroom',
        ]);
    }
}
