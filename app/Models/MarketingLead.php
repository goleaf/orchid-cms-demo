<?php

namespace App\Models;

use App\Enums\LeadStatus;
use App\Enums\LeadTaskStatus;
use Database\Factories\MarketingLeadFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MarketingLead extends Model
{
    /** @use HasFactory<MarketingLeadFactory> */
    use HasFactory;

    protected $fillable = [
        'marketing_campaign_id',
        'responsible_manager_id',
        'branch_id',
        'training_program_id',
        'training_group_id',
        'instructor_id',
        'converted_student_profile_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'messenger',
        'city',
        'source',
        'status',
        'license_category',
        'preferred_format',
        'preferred_language',
        'preferred_time',
        'budget_cents',
        'is_hot',
        'next_follow_up_at',
        'last_status_changed_at',
        'privacy_accepted_at',
        'contacted_at',
        'converted_at',
        'message',
        'rejection_reason',
        'lost_reason_code',
        'crm_snapshot',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_term',
        'utm_content',
        'referrer_url',
    ];

    protected $casts = [
        'status' => LeadStatus::class,
        'budget_cents' => 'integer',
        'is_hot' => 'boolean',
        'next_follow_up_at' => 'datetime',
        'last_status_changed_at' => 'datetime',
        'crm_snapshot' => 'array',
        'privacy_accepted_at' => 'datetime',
        'contacted_at' => 'datetime',
        'converted_at' => 'datetime',
    ];

    public function marketingCampaign(): BelongsTo
    {
        return $this->belongsTo(MarketingCampaign::class);
    }

    public function responsibleManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_manager_id');
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
        return trim($this->first_name.' '.($this->last_name ?? ''));
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
            'marketing_campaign_id',
            'responsible_manager_id',
            'branch_id',
            'training_program_id',
            'training_group_id',
            'instructor_id',
            'converted_student_profile_id',
            'first_name',
            'last_name',
            'email',
            'phone',
            'messenger',
            'city',
            'source',
            'status',
            'license_category',
            'preferred_format',
            'preferred_language',
            'preferred_time',
            'budget_cents',
            'is_hot',
            'next_follow_up_at',
            'last_status_changed_at',
            'contacted_at',
            'converted_at',
            'rejection_reason',
            'lost_reason_code',
            'created_at',
        ]);
    }

    public function scopeForCrmDetail(Builder $query): Builder
    {
        return $query->select([
            'id',
            'marketing_campaign_id',
            'responsible_manager_id',
            'branch_id',
            'training_program_id',
            'training_group_id',
            'instructor_id',
            'converted_student_profile_id',
            'first_name',
            'last_name',
            'email',
            'phone',
            'messenger',
            'city',
            'source',
            'status',
            'license_category',
            'preferred_format',
            'preferred_language',
            'preferred_time',
            'budget_cents',
            'is_hot',
            'next_follow_up_at',
            'last_status_changed_at',
            'privacy_accepted_at',
            'contacted_at',
            'converted_at',
            'message',
            'rejection_reason',
            'lost_reason_code',
            'crm_snapshot',
            'utm_source',
            'utm_medium',
            'utm_campaign',
            'utm_term',
            'utm_content',
            'referrer_url',
            'created_at',
            'updated_at',
        ]);
    }
}
