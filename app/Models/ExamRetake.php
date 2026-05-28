<?php

namespace App\Models;

use Database\Factories\ExamRetakeFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamRetake extends Model
{
    /** @use HasFactory<ExamRetakeFactory> */
    use HasFactory;

    protected $fillable = [
        'student_id',
        'enrollment_id',
        'previous_attempt_id',
        'new_attempt_id',
        'reason',
        'planned_at',
        'status',
    ];

    protected $casts = [
        'planned_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(StudentEnrollment::class, 'enrollment_id');
    }

    public function previousAttempt(): BelongsTo
    {
        return $this->belongsTo(ExamAttempt::class, 'previous_attempt_id');
    }

    public function newAttempt(): BelongsTo
    {
        return $this->belongsTo(ExamAttempt::class, 'new_attempt_id');
    }

    public function scopePlanned(Builder $query): Builder
    {
        return $query->where('status', 'planned');
    }

    public function scopeLinked(Builder $query): Builder
    {
        return $query->whereNotNull('new_attempt_id');
    }

    public function displayStatus(): string
    {
        return tkey('exams.retakes.statuses.'.$this->status) ?: $this->status;
    }
}
