<?php

namespace App\Models;

use App\Enums\LeadStatus;
use App\Enums\LeadTaskStatus;
use App\Support\Crm\PhoneNormalizer;
use BackedEnum;
use Closure;
use Database\Factories\MarketingLeadFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MarketingLead extends Model
{
    /** @use HasFactory<MarketingLeadFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'lead_number',
        'marketing_campaign_id',
        'responsible_manager_id',
        'assigned_by_user_id',
        'assigned_at',
        'branch_id',
        'training_program_id',
        'course_category_id',
        'training_group_id',
        'instructor_id',
        'converted_student_profile_id',
        'converted_enrollment_id',
        'created_by_user_id',
        'updated_by_user_id',
        'full_name',
        'first_name',
        'last_name',
        'middle_name',
        'email',
        'phone',
        'normalized_phone',
        'messenger',
        'city',
        'source',
        'status',
        'duplicate_of_id',
        'license_category',
        'preferred_format',
        'preferred_language',
        'preferred_time',
        'desired_start_date',
        'preferred_gearbox',
        'budget_cents',
        'is_hot',
        'priority',
        'lead_score',
        'next_follow_up_at',
        'last_status_changed_at',
        'privacy_accepted_at',
        'consent_accepted',
        'consent_accepted_at',
        'consent_text_version',
        'contacted_at',
        'last_contacted_at',
        'converted_at',
        'closed_at',
        'message',
        'internal_comment',
        'rejection_reason',
        'lost_reason_code',
        'crm_snapshot',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_term',
        'utm_content',
        'referrer_url',
        'landing_page',
        'form_page',
        'form_name',
        'locale',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'status' => LeadStatus::class,
        'budget_cents' => 'integer',
        'is_hot' => 'boolean',
        'lead_score' => 'integer',
        'next_follow_up_at' => 'datetime',
        'last_status_changed_at' => 'datetime',
        'crm_snapshot' => 'array',
        'assigned_at' => 'datetime',
        'desired_start_date' => 'date',
        'privacy_accepted_at' => 'datetime',
        'consent_accepted' => 'boolean',
        'consent_accepted_at' => 'datetime',
        'contacted_at' => 'datetime',
        'last_contacted_at' => 'datetime',
        'converted_at' => 'datetime',
        'closed_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function marketingCampaign(): BelongsTo
    {
        return $this->belongsTo(MarketingCampaign::class);
    }

    public function responsibleManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_manager_id');
    }

    public function manager(): BelongsTo
    {
        return $this->responsibleManager();
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by_user_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function creator(): BelongsTo
    {
        return $this->createdBy();
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
    }

    public function updater(): BelongsTo
    {
        return $this->updatedBy();
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(\App\Models\LeadStatus::class, 'status', 'code');
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(LeadSource::class, 'source', 'code');
    }

    public function lostReason(): BelongsTo
    {
        return $this->belongsTo(LeadLostReason::class, 'lost_reason_code', 'code');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
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
        return $this->belongsTo(CourseCategory::class);
    }

    public function trainingGroup(): BelongsTo
    {
        return $this->belongsTo(TrainingGroup::class);
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(Instructor::class);
    }

    public function convertedStudentProfile(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'converted_student_profile_id');
    }

    public function convertedStudent(): BelongsTo
    {
        return $this->convertedStudentProfile();
    }

    public function convertedEnrollment(): BelongsTo
    {
        return $this->belongsTo(StudentEnrollment::class, 'converted_enrollment_id');
    }

    public function duplicateOf(): BelongsTo
    {
        return $this->belongsTo(self::class, 'duplicate_of_id');
    }

    public function duplicates(): HasMany
    {
        return $this->hasMany(self::class, 'duplicate_of_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(
            LeadTag::class,
            'lead_tag_marketing_lead',
            'marketing_lead_id',
            'lead_tag_id',
        )
            ->withTimestamps();
    }

    public function activities(): HasMany
    {
        return $this->hasMany(MarketingLeadActivity::class, 'marketing_lead_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(MarketingLeadDocument::class, 'marketing_lead_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(MarketingLeadComment::class, 'marketing_lead_id');
    }

    public function communications(): HasMany
    {
        return $this->hasMany(MarketingLeadCommunication::class, 'marketing_lead_id');
    }

    public function communicationReminders(): HasMany
    {
        return $this->hasMany(CommunicationReminder::class, 'marketing_lead_id');
    }

    public function notificationDeliveryLogs(): HasMany
    {
        return $this->hasMany(NotificationDeliveryLog::class, 'marketing_lead_id');
    }

    public function notificationRecipients(): HasMany
    {
        return $this->hasMany(NotificationRecipient::class, 'lead_id');
    }

    public function notificationPreferences(): HasMany
    {
        return $this->hasMany(NotificationPreference::class, 'lead_id');
    }

    public function communicationThreads(): HasMany
    {
        return $this->hasMany(CommunicationThread::class, 'lead_id');
    }

    public function communicationMessages(): HasMany
    {
        return $this->hasMany(CommunicationMessage::class, 'lead_id');
    }

    public function notificationActivities(): HasMany
    {
        return $this->hasMany(NotificationActivity::class, 'lead_id');
    }

    public function callLogs(): HasMany
    {
        return $this->communications()->where('channel', 'phone');
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(MarketingLeadStatusHistory::class, 'marketing_lead_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(MarketingLeadTask::class, 'marketing_lead_id');
    }

    public function openTasks(): HasMany
    {
        return $this->tasks()->whereIn('status', [
            LeadTaskStatus::Open->value,
            LeadTaskStatus::InProgress->value,
        ]);
    }

    public function overdueTasks(): HasMany
    {
        return $this->openTasks()
            ->whereNotNull('due_at')
            ->where('due_at', '<', now());
    }

    public function fullName(): string
    {
        $name = collect([
            $this->first_name,
            $this->middle_name,
            $this->last_name,
        ])
            ->filter(fn (?string $part): bool => filled($part))
            ->implode(' ');

        if (filled($name)) {
            return $name;
        }

        return ($this->attributes['full_name'] ?? null)
            ?: tkey('crm.leads.fallback.lead');
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->fullName();
    }

    public function getDisplayContactAttribute(): string
    {
        return collect([$this->phone, $this->email])
            ->filter(fn (?string $value): bool => filled($value))
            ->implode(' / ') ?: '-';
    }

    public function getCourseIdAttribute(): ?int
    {
        return $this->training_program_id;
    }

    public function setCourseIdAttribute(?int $value): void
    {
        $this->attributes['training_program_id'] = $value;
    }

    public function getManagerIdAttribute(): ?int
    {
        return $this->responsible_manager_id;
    }

    public function setManagerIdAttribute(?int $value): void
    {
        $this->attributes['responsible_manager_id'] = $value;
    }

    public function getCreatedByIdAttribute(): ?int
    {
        return $this->created_by_user_id;
    }

    public function setCreatedByIdAttribute(?int $value): void
    {
        $this->attributes['created_by_user_id'] = $value;
    }

    public function getUpdatedByIdAttribute(): ?int
    {
        return $this->updated_by_user_id;
    }

    public function setUpdatedByIdAttribute(?int $value): void
    {
        $this->attributes['updated_by_user_id'] = $value;
    }

    public function getConvertedStudentIdAttribute(): ?int
    {
        return $this->converted_student_profile_id;
    }

    public function setConvertedStudentIdAttribute(?int $value): void
    {
        $this->attributes['converted_student_profile_id'] = $value;
    }

    public function getPreferredMessengerAttribute(): ?string
    {
        return $this->messenger;
    }

    public function setPreferredMessengerAttribute(?string $value): void
    {
        $this->attributes['messenger'] = $value;
    }

    public function getCommentAttribute(): ?string
    {
        return $this->message;
    }

    public function setCommentAttribute(?string $value): void
    {
        $this->attributes['message'] = $value;
    }

    public function getPreferredTrainingLanguageAttribute(): ?string
    {
        return $this->preferred_language;
    }

    public function setPreferredTrainingLanguageAttribute(?string $value): void
    {
        $this->attributes['preferred_language'] = $value;
    }

    public function getReferrerAttribute(): ?string
    {
        return $this->referrer_url;
    }

    public function setReferrerAttribute(?string $value): void
    {
        $this->attributes['referrer_url'] = $value;
    }

    public function getBudgetAttribute(): ?float
    {
        return $this->budget_cents === null
            ? null
            : round($this->budget_cents / 100, 2);
    }

    public function setBudgetAttribute(mixed $value): void
    {
        $this->attributes['budget_cents'] = filled($value)
            ? (int) round(((float) $value) * 100)
            : null;
    }

    public function setPhoneAttribute(?string $value): void
    {
        $this->attributes['phone'] = $value;
        $this->attributes['normalized_phone'] = PhoneNormalizer::normalize($value);
    }

    public function budgetForHumans(): string
    {
        if ($this->budget_cents === null) {
            return '-';
        }

        return number_format($this->budget_cents / 100, 2).' EUR';
    }

    public function scopeForLeadList(Builder $query): Builder
    {
        return $query->select([
            'id',
            'uuid',
            'lead_number',
            'marketing_campaign_id',
            'responsible_manager_id',
            'assigned_by_user_id',
            'assigned_at',
            'branch_id',
            'training_program_id',
            'course_category_id',
            'training_group_id',
            'instructor_id',
            'converted_student_profile_id',
            'converted_enrollment_id',
            'created_by_user_id',
            'updated_by_user_id',
            'full_name',
            'first_name',
            'last_name',
            'middle_name',
            'email',
            'phone',
            'normalized_phone',
            'messenger',
            'city',
            'source',
            'status',
            'duplicate_of_id',
            'license_category',
            'preferred_format',
            'preferred_language',
            'preferred_time',
            'desired_start_date',
            'preferred_gearbox',
            'budget_cents',
            'is_hot',
            'priority',
            'lead_score',
            'next_follow_up_at',
            'last_status_changed_at',
            'contacted_at',
            'last_contacted_at',
            'converted_at',
            'closed_at',
            'form_name',
            'form_page',
            'landing_page',
            'locale',
            'utm_source',
            'utm_medium',
            'utm_campaign',
            'utm_term',
            'utm_content',
            'referrer_url',
            'rejection_reason',
            'lost_reason_code',
            'internal_comment',
            'created_at',
        ]);
    }

    public function scopeForCrmDetail(Builder $query): Builder
    {
        return $query->select([
            'id',
            'uuid',
            'lead_number',
            'marketing_campaign_id',
            'responsible_manager_id',
            'assigned_by_user_id',
            'assigned_at',
            'branch_id',
            'training_program_id',
            'course_category_id',
            'training_group_id',
            'instructor_id',
            'converted_student_profile_id',
            'converted_enrollment_id',
            'created_by_user_id',
            'updated_by_user_id',
            'full_name',
            'first_name',
            'last_name',
            'middle_name',
            'email',
            'phone',
            'normalized_phone',
            'messenger',
            'city',
            'source',
            'status',
            'duplicate_of_id',
            'license_category',
            'preferred_format',
            'preferred_language',
            'preferred_time',
            'desired_start_date',
            'preferred_gearbox',
            'budget_cents',
            'is_hot',
            'priority',
            'lead_score',
            'next_follow_up_at',
            'last_status_changed_at',
            'privacy_accepted_at',
            'consent_accepted',
            'consent_accepted_at',
            'consent_text_version',
            'contacted_at',
            'last_contacted_at',
            'converted_at',
            'closed_at',
            'message',
            'internal_comment',
            'rejection_reason',
            'lost_reason_code',
            'crm_snapshot',
            'utm_source',
            'utm_medium',
            'utm_campaign',
            'utm_term',
            'utm_content',
            'referrer_url',
            'landing_page',
            'form_page',
            'form_name',
            'locale',
            'ip_address',
            'user_agent',
            'created_at',
            'updated_at',
        ]);
    }

    public function scopeMatchingSearch(Builder $query, string $search): Builder
    {
        $phoneToken = PhoneNormalizer::searchToken($search);

        return $query->where(function (Builder $query) use ($search, $phoneToken): void {
            $query
                ->where('lead_number', 'like', '%'.$search.'%')
                ->orWhere('full_name', 'like', '%'.$search.'%')
                ->orWhere('first_name', 'like', '%'.$search.'%')
                ->orWhere('last_name', 'like', '%'.$search.'%')
                ->orWhere('email', 'like', '%'.$search.'%')
                ->orWhere('phone', 'like', '%'.$search.'%')
                ->orWhere('uuid', 'like', '%'.$search.'%')
                ->orWhere('message', 'like', '%'.$search.'%')
                ->orWhere('internal_comment', 'like', '%'.$search.'%');

            if (is_numeric($search)) {
                $query->orWhere('id', (int) $search);
            }

            if (filled($phoneToken)) {
                $query->orWhere('normalized_phone', 'like', '%'.$phoneToken.'%');
            }
        });
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        return filled($search)
            ? $query->matchingSearch((string) $search)
            : $query;
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query
            ->whereNull('closed_at')
            ->whereIn('status', LeadStatus::openPipelineValues());
    }

    public function scopeClosed(Builder $query): Builder
    {
        return $query->where(function (Builder $query): void {
            $query
                ->whereNotNull('closed_at')
                ->orWhereIn('status', self::finalStatusValues());
        });
    }

    public function scopeNew(Builder $query): Builder
    {
        return $query->where('status', LeadStatus::New->value);
    }

    public function scopeAssignedTo(Builder $query, int|string $managerId): Builder
    {
        return $query->where('responsible_manager_id', $managerId);
    }

    public function scopeUnassigned(Builder $query): Builder
    {
        return $query->whereNull('responsible_manager_id');
    }

    public function scopeOverdueFollowUp(Builder $query): Builder
    {
        return $query
            ->open()
            ->whereNotNull('next_follow_up_at')
            ->where('next_follow_up_at', '<', now());
    }

    public function scopeDueToday(Builder $query): Builder
    {
        return $query
            ->open()
            ->whereBetween('next_follow_up_at', [now()->startOfDay(), now()->endOfDay()]);
    }

    public function scopeDuplicates(Builder $query): Builder
    {
        return $query->where(function (Builder $query): void {
            $query
                ->where('status', LeadStatus::Duplicate->value)
                ->orWhereNotNull('duplicate_of_id');
        });
    }

    public function scopeSpam(Builder $query): Builder
    {
        return $query->where('status', LeadStatus::Spam->value);
    }

    public function scopeLost(Builder $query): Builder
    {
        return $query->whereIn('status', [
            LeadStatus::Lost->value,
            LeadStatus::Rejected->value,
        ]);
    }

    public function scopeConverted(Builder $query): Builder
    {
        return $query->where(function (Builder $query): void {
            $query
                ->whereNotNull('converted_at')
                ->orWhereIn('status', self::successStatusValues());
        });
    }

    public function scopeNotConverted(Builder $query): Builder
    {
        return $query->whereNull('converted_at');
    }

    public function scopeByStatus(Builder $query, LeadStatus|string|null $status): Builder
    {
        return filled($status)
            ? $query->where('status', $status instanceof LeadStatus ? $status->value : $status)
            : $query;
    }

    public function scopeBySource(Builder $query, ?string $source): Builder
    {
        return filled($source) ? $query->where('source', $source) : $query;
    }

    public function scopeByManager(Builder $query, int|string|null $managerId): Builder
    {
        return filled($managerId) ? $query->where('responsible_manager_id', $managerId) : $query;
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

    public function isClosed(): bool
    {
        return $this->closed_at !== null || $this->status->isFinal();
    }

    public function getIsClosedAttribute(): bool
    {
        return $this->isClosed();
    }

    public function getIsConvertedAttribute(): bool
    {
        return $this->converted_at !== null || $this->status->isSuccess();
    }

    public function getIsDuplicateAttribute(): bool
    {
        return $this->duplicate_of_id !== null || $this->status === LeadStatus::Duplicate;
    }

    public function getIsSpamAttribute(): bool
    {
        return $this->status === LeadStatus::Spam;
    }

    public function getIsLostAttribute(): bool
    {
        return $this->status->isLost();
    }

    public function getIsOverdueAttribute(): bool
    {
        return ! $this->is_closed
            && $this->next_follow_up_at !== null
            && $this->next_follow_up_at->isPast();
    }

    public function getCanBeConvertedAttribute(): bool
    {
        return ! $this->is_converted
            && ! $this->is_duplicate
            && ! $this->is_spam
            && ! $this->is_lost;
    }

    public function priorityLabel(): string
    {
        return tkey('crm.leads.priorities.'.($this->priority ?: 'normal'));
    }

    /**
     * @return array<string, int>
     */
    public static function reportCountByStatus(?Closure $scope = null): array
    {
        return self::reportCountByColumn('status', $scope);
    }

    /**
     * @return array<string, int>
     */
    public static function reportCountBySource(?Closure $scope = null): array
    {
        return self::reportCountByColumn('source', $scope);
    }

    /**
     * @return array<string, int>
     */
    public static function reportCountByManager(?Closure $scope = null): array
    {
        return self::reportCountByColumn('responsible_manager_id', $scope, 'unassigned');
    }

    /**
     * @return array<string, int>
     */
    public static function reportCountByLostReason(?Closure $scope = null): array
    {
        return self::reportCountByColumn('lost_reason_code', $scope, 'none');
    }

    /**
     * @return array<string, int>
     */
    public static function reportCountByDay(?Closure $scope = null): array
    {
        $query = static::query()
            ->select(['id', 'created_at'])
            ->orderBy('created_at')
            ->orderBy('id');

        if ($scope !== null) {
            $scope($query);
        }

        $counts = [];

        foreach ($query->cursor() as $lead) {
            $date = $lead->created_at?->toDateString();

            if ($date === null) {
                continue;
            }

            $counts[$date] = ($counts[$date] ?? 0) + 1;
        }

        return $counts;
    }

    public static function reportConversionReadyCount(?Closure $scope = null): int
    {
        $query = static::query()->where('status', LeadStatus::ReadyToEnroll->value);

        if ($scope !== null) {
            $scope($query);
        }

        return $query->count();
    }

    public static function reportOverdueFollowUpCount(?Closure $scope = null): int
    {
        $query = static::query()->overdueFollowUp();

        if ($scope !== null) {
            $scope($query);
        }

        return $query->count();
    }

    /**
     * @return array<string, int>
     */
    private static function reportCountByColumn(string $column, ?Closure $scope = null, string $emptyKey = 'none'): array
    {
        $query = static::query()
            ->select(['id', $column])
            ->orderBy($column)
            ->orderBy('id');

        if ($scope !== null) {
            $scope($query);
        }

        $counts = [];

        foreach ($query->cursor() as $lead) {
            $value = $lead->getAttribute($column);

            if ($value instanceof BackedEnum) {
                $value = $value->value;
            }

            $key = filled($value) ? (string) $value : $emptyKey;
            $counts[$key] = ($counts[$key] ?? 0) + 1;
        }

        return $counts;
    }

    /**
     * @return array<int, string>
     */
    private static function finalStatusValues(): array
    {
        return collect(LeadStatus::cases())
            ->filter(fn (LeadStatus $status): bool => $status->isFinal())
            ->map(fn (LeadStatus $status): string => $status->value)
            ->values()
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private static function successStatusValues(): array
    {
        return collect(LeadStatus::cases())
            ->filter(fn (LeadStatus $status): bool => $status->isSuccess())
            ->map(fn (LeadStatus $status): string => $status->value)
            ->values()
            ->all();
    }
}
