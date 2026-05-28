<?php

namespace App\Models;

use Database\Factories\TrainingGroupActivityFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrainingGroupActivity extends Model
{
    /** @use HasFactory<TrainingGroupActivityFactory> */
    use HasFactory;

    protected $fillable = [
        'training_group_id',
        'student_id',
        'student_enrollment_id',
        'enrollment_id',
        'membership_id',
        'student_profile_id',
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

    protected static function booted(): void
    {
        static::saving(function (self $activity): void {
            $activity->student_id ??= $activity->student_profile_id;
            $activity->student_profile_id ??= $activity->student_id;
            $activity->student_enrollment_id ??= $activity->enrollment_id;
            $activity->enrollment_id ??= $activity->student_enrollment_id;
        });
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(TrainingGroup::class, 'training_group_id');
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(StudentEnrollment::class, 'enrollment_id');
    }

    public function membership(): BelongsTo
    {
        return $this->belongsTo(TrainingGroupMembership::class, 'membership_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_profile_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getDisplayTypeAttribute(): string
    {
        return tkey('education.activities.types.'.$this->type);
    }
}
