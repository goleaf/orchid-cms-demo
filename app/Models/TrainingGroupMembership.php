<?php

namespace App\Models;

use App\Enums\TrainingGroupMembershipStatus;
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

    public const STATUS_INVITED = 'invited';

    public const STATUS_PENDING = 'pending';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_LEFT = 'left';

    public const STATUS_WAITLISTED = 'waitlisted';

    public const STATUS_TRANSFERRED = 'transferred';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_REMOVED = 'removed';

    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'uuid',
        'training_group_id',
        'student_id',
        'student_profile_id',
        'student_enrollment_id',
        'enrollment_id',
        'status',
        'joined_at',
        'left_at',
        'transfer_from_group_id',
        'transfer_to_group_id',
        'transfer_reason',
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

            $membership->syncAliases();
        });

        static::saving(function (self $membership): void {
            $membership->syncAliases();
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

    public function transferFromGroup(): BelongsTo
    {
        return $this->belongsTo(TrainingGroup::class, 'transfer_from_group_id');
    }

    public function transferToGroup(): BelongsTo
    {
        return $this->belongsTo(TrainingGroup::class, 'transfer_to_group_id');
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
        return $query->where('status', self::STATUS_ACTIVE)->whereNull('left_at');
    }

    public function scopeWaitlisted(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_WAITLISTED);
    }

    public function scopeTransferred(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_TRANSFERRED);
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeByGroup(Builder $query, int|string|null $groupId): Builder
    {
        return filled($groupId) ? $query->where('training_group_id', $groupId) : $query;
    }

    public function scopeByStudent(Builder $query, int|string|null $studentId): Builder
    {
        return filled($studentId)
            ? $query->where(fn (Builder $query): Builder => $query
                ->where('student_profile_id', $studentId)
                ->orWhere('student_id', $studentId))
            : $query;
    }

    public function scopeByEnrollment(Builder $query, int|string|null $enrollmentId): Builder
    {
        return filled($enrollmentId)
            ? $query->where(fn (Builder $query): Builder => $query
                ->where('enrollment_id', $enrollmentId)
                ->orWhere('student_enrollment_id', $enrollmentId))
            : $query;
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->status === self::STATUS_ACTIVE && $this->left_at === null;
    }

    public function getIsWaitlistedAttribute(): bool
    {
        return $this->status === self::STATUS_WAITLISTED;
    }

    public function getIsTransferredAttribute(): bool
    {
        return $this->status === self::STATUS_TRANSFERRED;
    }

    public function statusLabel(): string
    {
        return tkey('education.groups.memberships.statuses.'.$this->status);
    }

    /**
     * @return array<int, string>
     */
    public static function statusValues(): array
    {
        return TrainingGroupMembershipStatus::values();
    }

    private function syncAliases(): void
    {
        $this->student_id ??= $this->student_profile_id;
        $this->student_profile_id ??= $this->student_id;
        $this->student_enrollment_id ??= $this->enrollment_id;
        $this->enrollment_id ??= $this->student_enrollment_id;
        $this->transfer_reason ??= $this->left_reason;
    }
}
