<?php

namespace App\Models;

use Database\Factories\ExamParticipantFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamParticipant extends Model
{
    /** @use HasFactory<ExamParticipantFactory> */
    use HasFactory;

    protected $fillable = [
        'exam_session_id',
        'student_id',
        'enrollment_id',
        'status',
        'admitted',
        'block_reason',
        'registered_at',
    ];

    protected $casts = [
        'admitted' => 'boolean',
        'registered_at' => 'datetime',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(ExamSession::class, 'exam_session_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(StudentEnrollment::class, 'enrollment_id');
    }

    public function scopeAdmitted(Builder $query): Builder
    {
        return $query->where('admitted', true);
    }

    public function scopeBlocked(Builder $query): Builder
    {
        return $query->where('admitted', false)->whereNotNull('block_reason');
    }

    public function scopeRegistered(Builder $query): Builder
    {
        return $query->whereNotNull('registered_at');
    }

    public function displayStatus(): string
    {
        return tkey('exams.participants.statuses.'.$this->status) ?: $this->status;
    }
}
