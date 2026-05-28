<?php

namespace App\Models;

use App\Enums\GroupStatus;
use App\Models\Concerns\HasTranslations;
use Database\Factories\TrainingGroupFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'training_program_id',
        'course_category_id',
        'instructor_id',
        'name',
        'name_translations',
        'description_translations',
        'schedule_summary_translations',
        'code',
        'status',
        'status_id',
        'capacity',
        'places_taken',
        'starts_on',
        'ends_on',
        'enrollment_closes_on',
        'meeting_days',
        'meeting_time',
        'end_time',
        'classroom',
        'learning_notes',
        'schedule_notes',
        'is_visible_on_site',
        'is_featured',
        'sort_order',
        'created_by_id',
        'updated_by_id',
    ];

    protected $casts = [
        'name_translations' => 'array',
        'description_translations' => 'array',
        'schedule_summary_translations' => 'array',
        'status' => GroupStatus::class,
        'capacity' => 'integer',
        'places_taken' => 'integer',
        'starts_on' => 'date',
        'ends_on' => 'date',
        'enrollment_closes_on' => 'date',
        'meeting_days' => 'array',
        'meeting_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'is_visible_on_site' => 'boolean',
        'is_featured' => 'boolean',
        'sort_order' => 'integer',
        'deleted_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $group): void {
            if (blank($group->uuid)) {
                $group->uuid = (string) Str::uuid();
            }
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
        return $this->belongsTo(LearningProgram::class, 'training_program_id');
    }

    public function statusRecord(): BelongsTo
    {
        return $this->belongsTo(TrainingGroupStatus::class, 'status_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'training_program_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(CourseCategory::class, 'course_category_id');
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(Instructor::class);
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

    public function schedulePatterns(): HasMany
    {
        return $this->hasMany(TrainingGroupSchedulePattern::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(TrainingGroupActivity::class);
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class, 'training_group_id');
    }

    public function seatsAvailable(): int
    {
        $taken = max((int) ($this->enrollments_count ?? 0), $this->places_taken);

        return max(0, $this->capacity - $taken);
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
            return (bool) $this->statusRecord->accepts_enrollments;
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
            ->whereIn('status', [
                GroupStatus::Planned->value,
                GroupStatus::Recruiting->value,
                GroupStatus::Open->value,
                GroupStatus::AlmostFull->value,
            ]);
    }

    public function scopeOpenForEnrollment(Builder $query): Builder
    {
        return $query
            ->visibleOnSite()
            ->whereColumn('places_taken', '<', 'capacity');
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
        return $query->whereColumn('places_taken', '<', 'capacity');
    }

    public function scopeByStatus(Builder $query, GroupStatus|string|null $status): Builder
    {
        return filled($status)
            ? $query->where('status', $status instanceof GroupStatus ? $status->value : $status)
            : $query;
    }

    public function scopeStartsAfter(Builder $query, mixed $date): Builder
    {
        return $query->where('starts_on', '>=', $date);
    }

    public function scopeByCourse(Builder $query, mixed $course): Builder
    {
        return $query->where('training_program_id', $course instanceof TrainingProgram ? $course->getKey() : $course);
    }

    public function scopeByBranch(Builder $query, mixed $branch): Builder
    {
        return $query->where('branch_id', $branch instanceof Branch ? $branch->getKey() : $branch);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('starts_on')->orderBy('name');
    }

    public function getAvailablePlacesAttribute(): int
    {
        return $this->seatsAvailable();
    }

    public function getIsFullAttribute(): bool
    {
        return $this->available_places <= 0;
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
            'training_program_id',
            'course_category_id',
            'instructor_id',
            'name',
            'name_translations',
            'description_translations',
            'schedule_summary_translations',
            'code',
            'group_number',
            'status',
            'status_id',
            'capacity',
            'places_taken',
            'starts_on',
            'ends_on',
            'enrollment_closes_on',
            'meeting_days',
            'meeting_time',
            'end_time',
            'classroom',
            'learning_notes',
            'schedule_notes',
            'is_visible_on_site',
            'is_featured',
            'sort_order',
        ]);
    }
}
