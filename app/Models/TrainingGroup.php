<?php

namespace App\Models;

use App\Enums\GroupStatus;
use App\Models\Concerns\HasTranslations;
use Database\Factories\TrainingGroupFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class TrainingGroup extends Model
{
    /** @use HasFactory<TrainingGroupFactory> */
    use HasFactory;

    use HasTranslations;
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'group_number',
        'branch_id',
        'course_id',
        'training_program_id',
        'course_category_id',
        'instructor_id',
        'name',
        'name_translations',
        'description_translations',
        'public_description_translations',
        'schedule_summary_translations',
        'code',
        'status',
        'status_id',
        'learning_program_id',
        'manager_id',
        'administrator_id',
        'teacher_id',
        'capacity',
        'capacity_total',
        'capacity_reserved',
        'capacity_taken',
        'capacity_waitlist',
        'places_taken',
        'starts_on',
        'ends_on',
        'start_date',
        'planned_end_date',
        'actual_end_date',
        'enrollment_closes_on',
        'meeting_days',
        'meeting_time',
        'end_time',
        'classroom',
        'timezone',
        'default_lesson_duration_minutes',
        'learning_notes',
        'schedule_notes',
        'notes',
        'internal_notes',
        'is_visible_on_site',
        'is_featured',
        'is_accepting_applications',
        'sort_order',
        'created_by_id',
        'updated_by_id',
    ];

    protected $casts = [
        'name_translations' => 'array',
        'description_translations' => 'array',
        'public_description_translations' => 'array',
        'schedule_summary_translations' => 'array',
        'status' => GroupStatus::class,
        'capacity' => 'integer',
        'capacity_total' => 'integer',
        'capacity_reserved' => 'integer',
        'capacity_taken' => 'integer',
        'capacity_waitlist' => 'integer',
        'places_taken' => 'integer',
        'starts_on' => 'date',
        'ends_on' => 'date',
        'start_date' => 'date',
        'planned_end_date' => 'date',
        'actual_end_date' => 'date',
        'enrollment_closes_on' => 'date',
        'meeting_days' => 'array',
        'meeting_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'default_lesson_duration_minutes' => 'integer',
        'is_visible_on_site' => 'boolean',
        'is_featured' => 'boolean',
        'is_accepting_applications' => 'boolean',
        'sort_order' => 'integer',
        'deleted_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $group): void {
            if (blank($group->uuid)) {
                $group->uuid = (string) Str::uuid();
            }

            $group->syncEducationAliases();
        });

        static::saving(function (self $group): void {
            $group->syncEducationAliases();
        });
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function trainingProgram(): BelongsTo
    {
        return $this->belongsTo(TrainingProgram::class);
    }

    public function learningProgram(): BelongsTo
    {
        return $this->belongsTo(LearningProgram::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(TrainingGroupStatus::class, 'status_id');
    }

    public function statusRecord(): BelongsTo
    {
        return $this->status();
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'training_program_id');
    }

    public function courseCategory(): BelongsTo
    {
        return $this->belongsTo(CourseCategory::class, 'course_category_id');
    }

    public function category(): BelongsTo
    {
        return $this->courseCategory();
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(Instructor::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function administrator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'administrator_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(TrainingGroupMembership::class);
    }

    public function activeMemberships(): HasMany
    {
        return $this->memberships()->active();
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'training_group_memberships', 'training_group_id', 'student_profile_id')
            ->withPivot(['enrollment_id', 'student_enrollment_id', 'status', 'joined_at', 'left_at'])
            ->withTimestamps();
    }

    public function schedulePatterns(): HasMany
    {
        return $this->hasMany(TrainingGroupSchedulePattern::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(TrainingGroupActivity::class);
    }

    public function examAdmissions(): HasMany
    {
        return $this->hasMany(ExamAdmission::class);
    }

    public function examSessions(): HasMany
    {
        return $this->hasMany(ExamSession::class);
    }

    public function examSessionAliases(): HasMany
    {
        return $this->hasMany(ExamSession::class, 'group_id');
    }

    public function examAttempts(): HasMany
    {
        return $this->hasMany(ExamAttempt::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_id');
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class, 'training_group_id');
    }

    public function seatsAvailable(): int
    {
        $capacity = $this->capacity_total ?? $this->capacity;
        $taken = max((int) ($this->enrollments_count ?? 0), (int) ($this->capacity_taken ?? $this->places_taken));

        return max(0, (int) $capacity - $taken - (int) ($this->capacity_reserved ?? 0));
    }

    public function displayName(?string $locale = null): string
    {
        return $this->getTranslation('name', $locale)
            ?: $this->name
            ?: $this->group_number
            ?: $this->code
            ?: (string) $this->getKey();
    }

    public function displayDescription(?string $locale = null): ?string
    {
        return $this->getTranslation('description', $locale);
    }

    public function displayScheduleSummary(?string $locale = null): ?string
    {
        return $this->getTranslation('schedule_summary', $locale);
    }

    public function acceptsPublicApplications(): bool
    {
        return $this->is_visible_on_site
            && $this->acceptsEnrollment();
    }

    public function acceptsEnrollment(): bool
    {
        if ($this->statusRecord !== null) {
            return (bool) ($this->statusRecord->is_open_for_enrollment || $this->statusRecord->accepts_enrollments);
        }

        return in_array($this->status, [
            GroupStatus::Planned,
            GroupStatus::Recruiting,
            GroupStatus::Open,
            GroupStatus::AlmostFull,
            GroupStatus::Active,
        ], true);
    }

    public function scopeVisibleOnSite(Builder $query): Builder
    {
        return $query
            ->where('is_visible_on_site', true)
            ->where(function (Builder $query): void {
                $query
                    ->whereIn('status', [
                        GroupStatus::Planned->value,
                        GroupStatus::Recruiting->value,
                        GroupStatus::Open->value,
                        GroupStatus::AlmostFull->value,
                    ])
                    ->orWhereHas('statusRecord', fn (Builder $query): Builder => $query->where('is_public', true));
            });
    }

    public function scopeOpenForEnrollment(Builder $query): Builder
    {
        return $query
            ->visibleOnSite()
            ->where(function (Builder $query): void {
                $query
                    ->where('is_accepting_applications', true)
                    ->orWhereHas('statusRecord', fn (Builder $query): Builder => $query->where('is_open_for_enrollment', true));
            })
            ->where(function (Builder $query): void {
                $query
                    ->whereColumn('places_taken', '<', 'capacity')
                    ->orWhereColumn('capacity_taken', '<', 'capacity_total');
            });
    }

    public function scopeAcceptingApplications(Builder $query): Builder
    {
        return $query->where('is_accepting_applications', true);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', [
            GroupStatus::Active->value,
            GroupStatus::InProgress->value,
        ]);
    }

    public function scopeRecruiting(Builder $query): Builder
    {
        return $query->where('status', GroupStatus::Recruiting->value);
    }

    public function scopeInProgress(Builder $query): Builder
    {
        return $query->whereIn('status', [
            GroupStatus::Active->value,
            GroupStatus::InProgress->value,
        ]);
    }

    public function scopeNotFull(Builder $query): Builder
    {
        return $query->where(function (Builder $query): void {
            $query
                ->whereColumn('places_taken', '<', 'capacity')
                ->orWhereColumn('capacity_taken', '<', 'capacity_total');
        });
    }

    public function scopeForCapacityMutation(Builder $query): Builder
    {
        return $query->select([
            'id',
            'course_id',
            'training_program_id',
            'capacity',
            'capacity_total',
            'capacity_reserved',
            'capacity_taken',
            'capacity_waitlist',
            'places_taken',
            'starts_on',
            'ends_on',
            'start_date',
            'planned_end_date',
            'is_visible_on_site',
            'is_accepting_applications',
            'status',
            'status_id',
            'updated_by_id',
        ]);
    }

    public function scopeByStatus(Builder $query, GroupStatus|string|null $status): Builder
    {
        if (blank($status)) {
            return $query;
        }

        $statusValue = $status instanceof GroupStatus ? $status->value : (string) $status;

        return $query->where(function (Builder $query) use ($statusValue): void {
            $query
                ->where('status', $statusValue)
                ->orWhereHas('statusRecord', fn (Builder $query): Builder => $query->where('code', $statusValue));
        });
    }

    public function scopeStartsAfter(Builder $query, mixed $date): Builder
    {
        return $query->where(function (Builder $query) use ($date): void {
            $query->where('start_date', '>=', $date)->orWhere('starts_on', '>=', $date);
        });
    }

    public function scopeByCourse(Builder $query, mixed $course): Builder
    {
        $courseId = $course instanceof TrainingProgram ? $course->getKey() : $course;

        return $query->where(function (Builder $query) use ($courseId): void {
            $query->where('training_program_id', $courseId)->orWhere('course_id', $courseId);
        });
    }

    public function scopeByCourseCategory(Builder $query, int|string|null $categoryId): Builder
    {
        return filled($categoryId) ? $query->where('course_category_id', $categoryId) : $query;
    }

    public function scopeByBranch(Builder $query, mixed $branch): Builder
    {
        return $query->where('branch_id', $branch instanceof Branch ? $branch->getKey() : $branch);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('starts_on')->orderBy('name');
    }

    public function scopeScheduled(Builder $query): Builder
    {
        return $query->where('status', 'scheduled');
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->whereIn('status', [GroupStatus::Completed->value, GroupStatus::Finished->value]);
    }

    public function scopeCancelled(Builder $query): Builder
    {
        return $query->where('status', GroupStatus::Cancelled->value);
    }

    public function scopeByManager(Builder $query, int|string|null $managerId): Builder
    {
        return filled($managerId) ? $query->where('manager_id', $managerId) : $query;
    }

    public function scopeByTeacher(Builder $query, int|string|null $teacherId): Builder
    {
        return filled($teacherId) ? $query->where('teacher_id', $teacherId) : $query;
    }

    public function scopeStartsBefore(Builder $query, mixed $date): Builder
    {
        return $query->where(function (Builder $query) use ($date): void {
            $query->where('start_date', '<=', $date)->orWhere('starts_on', '<=', $date);
        });
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
                ->orWhere('group_number', 'like', '%'.$search.'%')
                ->orWhere('code', 'like', '%'.$search.'%')
                ->orWhere('name', 'like', '%'.$search.'%')
                ->orWhere('name_translations', 'like', '%'.$search.'%');

            if (is_numeric($search)) {
                $query->orWhere('id', (int) $search);
            }
        });
    }

    public function getAvailablePlacesAttribute(): int
    {
        return $this->seatsAvailable();
    }

    public function getIsFullAttribute(): bool
    {
        return $this->available_places <= 0;
    }

    public function getIsAlmostFullAttribute(): bool
    {
        return ! $this->is_full && $this->capacity_percent >= 80;
    }

    public function getIsOpenForEnrollmentAttribute(): bool
    {
        return $this->acceptsEnrollment() && ! $this->is_full;
    }

    public function getIsActiveAttribute(): bool
    {
        return in_array($this->status, [GroupStatus::Active, GroupStatus::InProgress], true)
            || (bool) $this->statusRecord?->is_in_progress;
    }

    public function getIsVisiblePubliclyAttribute(): bool
    {
        return $this->is_visible_on_site && $this->is_open_for_enrollment;
    }

    public function getCapacityPercentAttribute(): int
    {
        $capacity = max(1, (int) ($this->capacity_total ?? $this->capacity));
        $taken = (int) ($this->capacity_taken ?? $this->places_taken);

        return min(100, (int) round(($taken / $capacity) * 100));
    }

    public function getDisplayCodeAttribute(): string
    {
        return $this->group_number ?: $this->code ?: (string) $this->getKey();
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->displayName();
    }

    public function getDisplayTitleAttribute(): string
    {
        return $this->displayName();
    }

    public function scopeOperationalList(Builder $query): Builder
    {
        return $query->select([
            'id',
            'branch_id',
            'course_id',
            'training_program_id',
            'course_category_id',
            'instructor_id',
            'name',
            'name_translations',
            'description_translations',
            'public_description_translations',
            'schedule_summary_translations',
            'code',
            'group_number',
            'status',
            'status_id',
            'learning_program_id',
            'manager_id',
            'administrator_id',
            'teacher_id',
            'capacity',
            'capacity_total',
            'capacity_reserved',
            'capacity_taken',
            'capacity_waitlist',
            'places_taken',
            'starts_on',
            'ends_on',
            'start_date',
            'planned_end_date',
            'actual_end_date',
            'enrollment_closes_on',
            'meeting_days',
            'meeting_time',
            'end_time',
            'classroom',
            'timezone',
            'default_lesson_duration_minutes',
            'learning_notes',
            'schedule_notes',
            'notes',
            'internal_notes',
            'is_visible_on_site',
            'is_featured',
            'is_accepting_applications',
            'sort_order',
        ]);
    }

    private function syncEducationAliases(): void
    {
        if ($this->getAttribute('course_id') === null || ($this->isDirty('training_program_id') && ! $this->isDirty('course_id'))) {
            $this->setAttribute('course_id', $this->getAttribute('training_program_id'));
        }

        if ($this->getAttribute('training_program_id') === null || ($this->isDirty('course_id') && ! $this->isDirty('training_program_id'))) {
            $this->setAttribute('training_program_id', $this->getAttribute('course_id'));
        }

        if ($this->getAttribute('capacity_total') === null || ($this->isDirty('capacity') && ! $this->isDirty('capacity_total'))) {
            $this->setAttribute('capacity_total', $this->getAttribute('capacity'));
        }

        if ($this->getAttribute('capacity') === null || ($this->isDirty('capacity_total') && ! $this->isDirty('capacity'))) {
            $this->setAttribute('capacity', $this->getAttribute('capacity_total') ?? 12);
        }

        if ($this->getAttribute('capacity_reserved') === null) {
            $this->setAttribute('capacity_reserved', 0);
        }

        if ($this->getAttribute('capacity_taken') === null || ($this->isDirty('places_taken') && ! $this->isDirty('capacity_taken'))) {
            $this->setAttribute('capacity_taken', $this->getAttribute('places_taken') ?? 0);
        }

        if ($this->getAttribute('capacity_waitlist') === null) {
            $this->setAttribute('capacity_waitlist', 0);
        }

        if ($this->getAttribute('places_taken') === null || ($this->isDirty('capacity_taken') && ! $this->isDirty('places_taken'))) {
            $this->setAttribute('places_taken', $this->getAttribute('capacity_taken') ?? 0);
        }

        if ($this->getAttribute('start_date') === null || ($this->isDirty('starts_on') && ! $this->isDirty('start_date'))) {
            $this->setAttribute('start_date', $this->getAttribute('starts_on'));
        }

        if ($this->getAttribute('starts_on') === null || ($this->isDirty('start_date') && ! $this->isDirty('starts_on'))) {
            $this->setAttribute('starts_on', $this->getAttribute('start_date'));
        }

        if ($this->getAttribute('planned_end_date') === null || ($this->isDirty('ends_on') && ! $this->isDirty('planned_end_date'))) {
            $this->setAttribute('planned_end_date', $this->getAttribute('ends_on'));
        }

        if ($this->getAttribute('ends_on') === null || ($this->isDirty('planned_end_date') && ! $this->isDirty('ends_on'))) {
            $this->setAttribute('ends_on', $this->getAttribute('planned_end_date'));
        }

        if ($this->getAttribute('is_accepting_applications') === null) {
            $this->setAttribute('is_accepting_applications', $this->is_visible_on_site && $this->acceptsEnrollment());
        }
    }
}
