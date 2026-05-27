<?php

namespace App\Models;

use App\Enums\EnrollmentStatus;
use Database\Factories\EnrollmentFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Enrollment extends Model
{
    /** @use HasFactory<EnrollmentFactory> */
    use HasFactory;

    protected $fillable = [
        'student_profile_id',
        'training_program_id',
        'training_group_id',
        'instructor_id',
        'status',
        'started_at',
        'completed_at',
        'contracted_price_cents',
        'paid_cents',
    ];

    protected $casts = [
        'status' => EnrollmentStatus::class,
        'started_at' => 'date',
        'completed_at' => 'date',
        'contracted_price_cents' => 'integer',
        'paid_cents' => 'integer',
    ];

    public function studentProfile(): BelongsTo
    {
        return $this->belongsTo(StudentProfile::class);
    }

    public function trainingProgram(): BelongsTo
    {
        return $this->belongsTo(TrainingProgram::class);
    }

    public function trainingGroup(): BelongsTo
    {
        return $this->belongsTo(TrainingGroup::class);
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(Instructor::class);
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(DrivingLesson::class);
    }

    public function exams(): HasMany
    {
        return $this->hasMany(Exam::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(StudentDocument::class);
    }

    public function balanceCents(): int
    {
        return max(0, $this->contracted_price_cents - $this->paid_cents);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', EnrollmentStatus::Active->value);
    }

    public function scopeForAdminList(Builder $query): Builder
    {
        return $query->select([
            'id',
            'student_profile_id',
            'training_program_id',
            'training_group_id',
            'instructor_id',
            'status',
            'started_at',
            'completed_at',
            'contracted_price_cents',
            'paid_cents',
        ]);
    }
}
