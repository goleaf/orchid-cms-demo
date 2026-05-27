<?php

namespace App\Models;

use App\Enums\ExamStatus;
use Database\Factories\ExamFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Exam extends Model
{
    /** @use HasFactory<ExamFactory> */
    use HasFactory;

    protected $fillable = [
        'enrollment_id',
        'instructor_id',
        'exam_type',
        'status',
        'scheduled_at',
        'attempt_number',
        'score',
        'notes',
    ];

    protected $casts = [
        'status' => ExamStatus::class,
        'scheduled_at' => 'datetime',
        'attempt_number' => 'integer',
        'score' => 'integer',
    ];

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(Instructor::class);
    }

    public function scopeForExamList(Builder $query): Builder
    {
        return $query->select([
            'id',
            'enrollment_id',
            'instructor_id',
            'exam_type',
            'status',
            'scheduled_at',
            'attempt_number',
            'score',
        ]);
    }
}
