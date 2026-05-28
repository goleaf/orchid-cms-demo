<?php

namespace App\Models;

use App\Enums\ExamAttemptStatus;
use App\Enums\ExamType;
use Database\Factories\ExamAttemptFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ExamAttempt extends Model
{
    /** @use HasFactory<ExamAttemptFactory> */
    use HasFactory;

    protected $fillable = [
        'uuid',
        'exam_admission_id',
        'exam_session_id',
        'enrollment_id',
        'student_profile_id',
        'training_group_id',
        'training_program_id',
        'instructor_id',
        'driving_lesson_id',
        'student_document_id',
        'payment_id',
        'retake_of_attempt_id',
        'exam_type',
        'provider',
        'status',
        'attempt_number',
        'score',
        'max_score',
        'passed',
        'result_payload',
        'started_at',
        'finished_at',
        'next_eligible_at',
        'official_reference',
        'official_payload',
        'notes',
        'internal_notes',
        'created_by_id',
        'updated_by_id',
    ];

    protected $casts = [
        'exam_type' => ExamType::class,
        'status' => ExamAttemptStatus::class,
        'attempt_number' => 'integer',
        'score' => 'decimal:2',
        'max_score' => 'decimal:2',
        'passed' => 'boolean',
        'result_payload' => 'array',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'next_eligible_at' => 'datetime',
        'official_payload' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $attempt): void {
            if (blank($attempt->uuid)) {
                $attempt->uuid = (string) Str::uuid();
            }
        });
    }

    public function admission(): BelongsTo
    {
        return $this->belongsTo(ExamAdmission::class, 'exam_admission_id');
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(ExamSession::class, 'exam_session_id');
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(StudentEnrollment::class, 'enrollment_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_profile_id');
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(TrainingGroup::class, 'training_group_id');
    }

    public function trainingProgram(): BelongsTo
    {
        return $this->belongsTo(TrainingProgram::class);
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(Instructor::class);
    }

    public function drivingLesson(): BelongsTo
    {
        return $this->belongsTo(DrivingLesson::class);
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(StudentDocument::class, 'student_document_id');
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function retakeOf(): BelongsTo
    {
        return $this->belongsTo(self::class, 'retake_of_attempt_id');
    }

    public function retakes(): HasMany
    {
        return $this->hasMany(self::class, 'retake_of_attempt_id');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(ExamActivity::class);
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->whereIn('status', [
            ExamAttemptStatus::Passed->value,
            ExamAttemptStatus::Failed->value,
            ExamAttemptStatus::NoShow->value,
            ExamAttemptStatus::Cancelled->value,
        ]);
    }
}
