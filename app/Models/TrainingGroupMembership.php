<?php

namespace App\Models;

use Database\Factories\TrainingGroupMembershipFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class TrainingGroupMembership extends Model
{
    /** @use HasFactory<TrainingGroupMembershipFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'training_group_id',
        'student_profile_id',
        'enrollment_id',
        'status',
        'joined_at',
        'left_at',
        'left_reason',
        'notes',
        'created_by_id',
        'updated_by_id',
    ];

    protected $casts = [
        'joined_at' => 'datetime',
        'left_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $membership): void {
            if (blank($membership->uuid)) {
                $membership->uuid = (string) Str::uuid();
            }

            if ($membership->joined_at === null) {
                $membership->joined_at = now();
            }
        });
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(TrainingGroup::class, 'training_group_id');
    }

    public function trainingGroup(): BelongsTo
    {
        return $this->group();
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_profile_id');
    }

    public function studentProfile(): BelongsTo
    {
        return $this->belongsTo(StudentProfile::class, 'student_profile_id');
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(StudentEnrollment::class, 'enrollment_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active')->whereNull('left_at');
    }

    public function scopeByGroup(Builder $query, int|string|null $groupId): Builder
    {
        return filled($groupId) ? $query->where('training_group_id', $groupId) : $query;
    }

    public function scopeByStudent(Builder $query, int|string|null $studentId): Builder
    {
        return filled($studentId) ? $query->where('student_profile_id', $studentId) : $query;
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'active' && $this->left_at === null;
    }
}
