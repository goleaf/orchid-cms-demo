<?php

namespace App\Models;

use App\Enums\LeadStatus;
use App\Enums\LeadTaskStatus;
use App\Support\Crm\PhoneNormalizer;
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
        'marketing_campaign_id',
        'responsible_manager_id',
        'assigned_by_user_id',
        'assigned_at',
        'branch_id',
        'training_program_id',
        'training_group_id',
        'instructor_id',
        'converted_student_profile_id',
        'created_by_user_id',
        'updated_by_user_id',
        'first_name',
        'last_name',
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

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by_user_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
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

    public function convertedStudentProfile(): BelongsTo
    {
        return $this->belongsTo(StudentProfile::class, 'converted_student_profile_id');
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
        return $this->belongsToMany(LeadTag::class, 'lead_tag_marketing_lead')
            ->withTimestamps();
    }

    public function activities(): HasMany
    {
        return $this->hasMany(MarketingLeadActivity::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(MarketingLeadDocument::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(MarketingLeadComment::class);
    }

    public function communications(): HasMany
    {
        return $this->hasMany(MarketingLeadCommunication::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(MarketingLeadStatusHistory::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(MarketingLeadTask::class);
    }

    public function openTasks(): HasMany
    {
        return $this->tasks()->where('status', LeadTaskStatus::Open->value);
    }

    public function overdueTasks(): HasMany
    {
        return $this->openTasks()
            ->whereNotNull('due_at')
            ->where('due_at', '<', now());
    }

    public function fullName(): string
    {
        return trim($this->first_name.' '.($this->last_name ?? ''))
            ?: tkey('crm.leads.fallback.lead');
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
            'marketing_campaign_id',
            'responsible_manager_id',
            'assigned_by_user_id',
            'assigned_at',
            'branch_id',
            'training_program_id',
            'training_group_id',
            'instructor_id',
            'converted_student_profile_id',
            'created_by_user_id',
            'updated_by_user_id',
            'first_name',
            'last_name',
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
            'locale',
            'utm_source',
            'utm_campaign',
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
            'marketing_campaign_id',
            'responsible_manager_id',
            'assigned_by_user_id',
            'assigned_at',
            'branch_id',
            'training_program_id',
            'training_group_id',
            'instructor_id',
            'converted_student_profile_id',
            'created_by_user_id',
            'updated_by_user_id',
            'first_name',
            'last_name',
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
                ->where('first_name', 'like', '%'.$search.'%')
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

    public function isClosed(): bool
    {
        return $this->closed_at !== null || $this->status->isFinal();
    }

    public function priorityLabel(): string
    {
        return tkey('crm.leads.priorities.'.($this->priority ?: 'normal'));
    }
}
