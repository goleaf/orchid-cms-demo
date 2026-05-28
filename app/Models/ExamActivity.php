<?php

namespace App\Models;

use Database\Factories\ExamActivityFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamActivity extends Model
{
    /** @use HasFactory<ExamActivityFactory> */
    use HasFactory;

    protected $fillable = [
        'exam_admission_id',
        'exam_session_id',
        'exam_attempt_id',
        'attempt_id',
        'enrollment_id',
        'student_profile_id',
        'student_id',
        'training_group_id',
        'user_id',
        'type',
        'title',
        'body',
        'old_value',
        'new_value',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function admission(): BelongsTo
    {
        return $this->belongsTo(ExamAdmission::class, 'exam_admission_id');
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(ExamSession::class, 'exam_session_id');
    }

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(ExamAttempt::class, 'exam_attempt_id');
    }

    public function attemptAlias(): BelongsTo
    {
        return $this->belongsTo(ExamAttempt::class, 'attempt_id');
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(StudentEnrollment::class, 'enrollment_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_profile_id');
    }

    public function studentAlias(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(TrainingGroup::class, 'training_group_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getDisplayTypeAttribute(): string
    {
        return tkey('exams.activities.types.'.$this->type);
    }
}
