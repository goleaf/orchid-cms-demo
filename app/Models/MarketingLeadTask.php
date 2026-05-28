<?php

namespace App\Models;

use App\Enums\LeadTaskPriority;
use App\Enums\LeadTaskStatus;
use App\Models\Concerns\HasTranslations;
use Database\Factories\MarketingLeadTaskFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MarketingLeadTask extends Model
{
    /** @use HasFactory<MarketingLeadTaskFactory> */
    use HasFactory;
    use HasTranslations;
    use SoftDeletes;

    protected $fillable = [
        'marketing_lead_id',
        'assigned_to_user_id',
        'created_by_user_id',
        'title',
        'title_translations',
        'description_translations',
        'status',
        'priority',
        'due_at',
        'completed_at',
        'cancelled_at',
        'notes',
    ];

    protected $casts = [
        'title_translations' => 'array',
        'description_translations' => 'array',
        'status' => LeadTaskStatus::class,
        'priority' => LeadTaskPriority::class,
        'due_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function marketingLead(): BelongsTo
    {
        return $this->belongsTo(MarketingLead::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class, 'marketing_lead_id');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereIn('status', [
            LeadTaskStatus::Open->value,
            LeadTaskStatus::InProgress->value,
        ]);
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query
            ->open()
            ->whereNotNull('due_at')
            ->where('due_at', '<', now());
    }

    public function scopeDueToday(Builder $query): Builder
    {
        return $query
            ->open()
            ->whereBetween('due_at', [now()->startOfDay(), now()->endOfDay()]);
    }

    public function isOverdue(): bool
    {
        return in_array($this->status, [LeadTaskStatus::Open, LeadTaskStatus::InProgress], true)
            && $this->due_at !== null
            && $this->due_at->isPast();
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->isOverdue();
    }

    public function getDisplayTitleAttribute(): string
    {
        return $this->getTranslation('title')
            ?: $this->title;
    }
}
