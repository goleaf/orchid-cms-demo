<?php

namespace App\Models;

use App\Enums\ExamAdmissionStatus;
use App\Enums\ExamChecklistItemStatus;
use App\Enums\ExamType;
use Database\Factories\ExamAdmissionFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ExamAdmission extends Model
{
    /** @use HasFactory<ExamAdmissionFactory> */
    use HasFactory;

    protected $fillable = [
        'uuid',
        'enrollment_id',
        'student_profile_id',
        'training_group_id',
        'training_program_id',
        'branch_id',
        'instructor_id',
        'admission_type',
        'status',
        'required_theory_hours',
        'completed_theory_hours',
        'required_practice_hours',
        'completed_practice_hours',
        'documents_status',
        'payment_status',
        'checklist_status',
        'admitted_at',
        'rejected_at',
        'expires_at',
        'notes',
        'internal_notes',
        'meta',
        'created_by_id',
        'updated_by_id',
    ];

    protected $casts = [
        'admission_type' => ExamType::class,
        'status' => ExamAdmissionStatus::class,
        'required_theory_hours' => 'decimal:2',
        'completed_theory_hours' => 'decimal:2',
        'required_practice_hours' => 'decimal:2',
        'completed_practice_hours' => 'decimal:2',
        'admitted_at' => 'datetime',
        'rejected_at' => 'datetime',
        'expires_at' => 'datetime',
        'meta' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $admission): void {
            if (blank($admission->uuid)) {
                $admission->uuid = (string) Str::uuid();
            }
        });
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

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(Instructor::class);
    }

    public function checklistItems(): HasMany
    {
        return $this->hasMany(ExamAdmissionChecklistItem::class);
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(ExamAttempt::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(ExamActivity::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_id');
    }

    public function isReady(): bool
    {
        if (! $this->status->allowsAttempt()) {
            return false;
        }

        if ($this->relationLoaded('checklistItems')) {
            return $this->checklistItems
                ->every(fn (ExamAdmissionChecklistItem $item): bool => $item->status->clearsAdmission());
        }

        return ! $this->checklistItems()
            ->whereNotIn('status', [
                ExamChecklistItemStatus::Passed->value,
                ExamChecklistItemStatus::Waived->value,
            ])
            ->exists();
    }

    public function scopeReady(Builder $query): Builder
    {
        return $query->whereIn('status', [
            ExamAdmissionStatus::Ready->value,
            ExamAdmissionStatus::Admitted->value,
            ExamAdmissionStatus::RetakeScheduled->value,
        ]);
    }

    public function scopeForExamDashboard(Builder $query): Builder
    {
        return $query->select([
            'id',
            'uuid',
            'enrollment_id',
            'student_profile_id',
            'training_group_id',
            'training_program_id',
            'branch_id',
            'instructor_id',
            'admission_type',
            'status',
            'checklist_status',
            'admitted_at',
            'expires_at',
            'created_at',
        ]);
    }
}
