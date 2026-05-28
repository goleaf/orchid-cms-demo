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
use Illuminate\Database\Eloquent\Relations\HasOne;
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
        'student_id',
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
        'status_id',
        'attempt_number',
        'attempt_no',
        'score',
        'max_score',
        'passed',
        'no_show',
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
        'attempt_no' => 'integer',
        'score' => 'decimal:2',
        'max_score' => 'decimal:2',
        'passed' => 'boolean',
        'no_show' => 'boolean',
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

            if ($attempt->student_id === null && $attempt->student_profile_id !== null) {
                $attempt->student_id = $attempt->student_profile_id;
            }

            if ($attempt->student_profile_id === null && $attempt->student_id !== null) {
                $attempt->student_profile_id = $attempt->student_id;
            }

            if ($attempt->attempt_no === null && $attempt->attempt_number !== null) {
                $attempt->attempt_no = $attempt->attempt_number;
            }

            if ($attempt->attempt_number === null && $attempt->attempt_no !== null) {
                $attempt->attempt_number = $attempt->attempt_no;
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

    public function statusRecord(): BelongsTo
    {
        return $this->belongsTo(\App\Models\ExamAttemptStatus::class, 'status_id');
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

    public function result(): HasOne
    {
        return $this->hasOne(ExamResult::class, 'attempt_id');
    }

    public function previousRetake(): HasOne
    {
        return $this->hasOne(ExamRetake::class, 'previous_attempt_id');
    }

    public function newRetake(): HasOne
    {
        return $this->hasOne(ExamRetake::class, 'new_attempt_id');
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

    public function scopePassed(Builder $query): Builder
    {
        return $query->where('passed', true);
    }

    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', ExamAttemptStatus::Failed->value);
    }

    public function scopeNoShow(Builder $query): Builder
    {
        return $query->where(function (Builder $query): void {
            $query->where('no_show', true)
                ->orWhere('status', ExamAttemptStatus::NoShow->value);
        });
    }

    public function scopeInProgress(Builder $query): Builder
    {
        return $query->where('status', ExamAttemptStatus::InProgress->value);
    }

    public function displayStatus(?string $locale = null): string
    {
        return $this->statusRecord?->displayName($locale)
            ?: tkey('exams.attempt_statuses.'.$this->status->value)
            ?: $this->status->value;
    }
}
