<?php

namespace App\Models;

use App\Enums\EnrollmentStatus;
use Database\Factories\EnrollmentFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Enrollment extends Model
{
    /** @use HasFactory<EnrollmentFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'enrollment_number',
        'student_profile_id',
        'lead_id',
        'training_program_id',
        'course_category_id',
        'branch_id',
        'training_group_id',
        'status_id',
        'manager_id',
        'administrator_id',
        'instructor_id',
        'teacher_id',
        'status',
        'started_at',
        'start_date',
        'planned_end_date',
        'actual_end_date',
        'completed_at',
        'preferred_time',
        'training_language',
        'format',
        'gearbox_type',
        'contracted_price_cents',
        'paid_cents',
        'price',
        'discount',
        'currency',
        'payment_status',
        'theory_progress',
        'practice_progress',
        'total_theory_hours',
        'completed_theory_hours',
        'total_practice_hours',
        'completed_practice_hours',
        'notes',
        'internal_notes',
        'created_by_id',
        'updated_by_id',
    ];

    protected $casts = [
        'status' => EnrollmentStatus::class,
        'started_at' => 'date',
        'start_date' => 'date',
        'planned_end_date' => 'date',
        'actual_end_date' => 'date',
        'completed_at' => 'date',
        'contracted_price_cents' => 'integer',
        'paid_cents' => 'integer',
        'price' => 'decimal:2',
        'discount' => 'decimal:2',
        'theory_progress' => 'decimal:2',
        'practice_progress' => 'decimal:2',
        'total_theory_hours' => 'decimal:2',
        'completed_theory_hours' => 'decimal:2',
        'total_practice_hours' => 'decimal:2',
        'completed_practice_hours' => 'decimal:2',
        'deleted_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $enrollment): void {
            if (blank($enrollment->uuid)) {
                $enrollment->uuid = (string) Str::uuid();
            }

            if ($enrollment->start_date === null && $enrollment->started_at !== null) {
                $enrollment->start_date = $enrollment->started_at;
            }
        });
    }

    public function studentProfile(): BelongsTo
    {
        return $this->belongsTo(StudentProfile::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_profile_id');
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class, 'lead_id');
    }

    public function trainingProgram(): BelongsTo
    {
        return $this->belongsTo(TrainingProgram::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'training_program_id');
    }

    public function courseCategory(): BelongsTo
    {
        return $this->belongsTo(CourseCategory::class, 'course_category_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function trainingGroup(): BelongsTo
    {
        return $this->belongsTo(TrainingGroup::class);
    }

    public function groupMemberships(): HasMany
    {
        return $this->hasMany(TrainingGroupMembership::class, 'enrollment_id');
    }

    public function activeGroupMembership(): HasOne
    {
        return $this->hasOne(TrainingGroupMembership::class, 'enrollment_id')->active();
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(\App\Models\EnrollmentStatus::class, 'status_id');
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function administrator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'administrator_id');
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(Instructor::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(DrivingLesson::class);
    }

    public function exams(): HasMany
    {
        return $this->hasMany(Exam::class);
    }

    public function examAdmissions(): HasMany
    {
        return $this->hasMany(ExamAdmission::class, 'enrollment_id');
    }

    public function examAttempts(): HasMany
    {
        return $this->hasMany(ExamAttempt::class, 'enrollment_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(StudentDocument::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(StudentActivity::class, 'enrollment_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(StudentTask::class, 'enrollment_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_id');
    }

    public function balanceCents(): int
    {
        return max(0, $this->contracted_price_cents - $this->paid_cents);
    }

    public function getStudentIdAttribute(): ?int
    {
        return $this->student_profile_id;
    }

    public function setStudentIdAttribute(?int $value): void
    {
        $this->attributes['student_profile_id'] = $value;
    }

    public function getCourseIdAttribute(): ?int
    {
        return $this->training_program_id;
    }

    public function setCourseIdAttribute(?int $value): void
    {
        $this->attributes['training_program_id'] = $value;
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->enrollment_number
            ?: $this->trainingProgram?->displayTitle()
            ?: tkey('students.enrollments.fallback.enrollment');
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->status->isActiveWorkflow();
    }

    public function getIsCompletedAttribute(): bool
    {
        return $this->status->isCompleted();
    }

    public function getIsCancelledAttribute(): bool
    {
        return $this->status->isCancelled();
    }

    public function getPaymentStatusLabelAttribute(): string
    {
        return filled($this->payment_status)
            ? tkey('students.enrollments.payment_statuses.'.$this->payment_status)
            : '-';
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', [
            EnrollmentStatus::Pending->value,
            EnrollmentStatus::WaitingDocuments->value,
            EnrollmentStatus::WaitingPayment->value,
            EnrollmentStatus::WaitingStart->value,
            EnrollmentStatus::Active->value,
            EnrollmentStatus::Theory->value,
            EnrollmentStatus::Practice->value,
            EnrollmentStatus::ReadyInternalExam->value,
            EnrollmentStatus::ReadyStateExam->value,
        ]);
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', EnrollmentStatus::Completed->value);
    }

    public function scopeCancelled(Builder $query): Builder
    {
        return $query->whereIn('status', [
            EnrollmentStatus::Cancelled->value,
            EnrollmentStatus::Expelled->value,
        ]);
    }

    public function scopeByStatus(Builder $query, EnrollmentStatus|string|null $status): Builder
    {
        return filled($status)
            ? $query->where('status', $status instanceof EnrollmentStatus ? $status->value : $status)
            : $query;
    }

    public function scopeByCourse(Builder $query, int|string|null $courseId): Builder
    {
        return filled($courseId) ? $query->where('training_program_id', $courseId) : $query;
    }

    public function scopeByBranch(Builder $query, int|string|null $branchId): Builder
    {
        return filled($branchId) ? $query->where('branch_id', $branchId) : $query;
    }

    public function scopeByTrainingGroup(Builder $query, int|string|null $trainingGroupId): Builder
    {
        return filled($trainingGroupId) ? $query->where('training_group_id', $trainingGroupId) : $query;
    }

    public function scopeWaitingDocuments(Builder $query): Builder
    {
        return $query->where('status', EnrollmentStatus::WaitingDocuments->value);
    }

    public function scopeWaitingPayment(Builder $query): Builder
    {
        return $query->where('status', EnrollmentStatus::WaitingPayment->value);
    }

    public function scopeWaitingStart(Builder $query): Builder
    {
        return $query->where('status', EnrollmentStatus::WaitingStart->value);
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if (blank($search)) {
            return $query;
        }

        $search = (string) $search;

        return $query->where(function (Builder $query) use ($search): void {
            $query
                ->where('uuid', 'like', '%'.$search.'%')
                ->orWhere('enrollment_number', 'like', '%'.$search.'%')
                ->orWhere('notes', 'like', '%'.$search.'%')
                ->orWhere('internal_notes', 'like', '%'.$search.'%');

            if (is_numeric($search)) {
                $query->orWhere('id', (int) $search);
            }
        });
    }

    public function scopeForAdminList(Builder $query): Builder
    {
        return $query->select([
            'id',
            'uuid',
            'enrollment_number',
            'student_profile_id',
            'lead_id',
            'training_program_id',
            'course_category_id',
            'branch_id',
            'training_group_id',
            'status_id',
            'manager_id',
            'administrator_id',
            'instructor_id',
            'teacher_id',
            'status',
            'started_at',
            'start_date',
            'planned_end_date',
            'actual_end_date',
            'completed_at',
            'contracted_price_cents',
            'paid_cents',
            'price',
            'discount',
            'currency',
            'payment_status',
        ]);
    }
}
