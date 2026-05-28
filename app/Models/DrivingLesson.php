<?php

namespace App\Models;

use App\Enums\LessonStatus;
use Database\Factories\DrivingLessonFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DrivingLesson extends Model
{
    /** @use HasFactory<DrivingLessonFactory> */
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'enrollment_id',
        'instructor_id',
        'vehicle_id',
        'lesson_type',
        'status',
        'starts_at',
        'ends_at',
        'topic',
        'location',
        'notes',
    ];

    protected $casts = [
        'status' => LessonStatus::class,
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(Instructor::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function examAttempts(): HasMany
    {
        return $this->hasMany(ExamAttempt::class);
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query
            ->where('status', LessonStatus::Scheduled->value)
            ->where('starts_at', '>=', now())
            ->orderBy('starts_at');
    }

    public function scopeForScheduleList(Builder $query): Builder
    {
        return $query->select([
            'id',
            'branch_id',
            'enrollment_id',
            'instructor_id',
            'vehicle_id',
            'lesson_type',
            'status',
            'starts_at',
            'ends_at',
            'topic',
            'location',
        ]);
    }
}
