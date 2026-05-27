<?php

namespace App\Models;

use App\Enums\LeadTaskPriority;
use App\Enums\LeadTaskStatus;
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
    use SoftDeletes;

    protected $fillable = [
        'marketing_lead_id',
        'assigned_to_user_id',
        'created_by_user_id',
        'title',
        'status',
        'priority',
        'due_at',
        'completed_at',
        'notes',
    ];

    protected $casts = [
        'status' => LeadTaskStatus::class,
        'priority' => LeadTaskPriority::class,
        'due_at' => 'datetime',
        'completed_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function marketingLead(): BelongsTo
    {
        return $this->belongsTo(MarketingLead::class);
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
        return $query->where('status', LeadTaskStatus::Open->value);
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query
            ->open()
            ->whereNotNull('due_at')
            ->where('due_at', '<', now());
    }

    public function isOverdue(): bool
    {
        return $this->status !== LeadTaskStatus::Done
            && $this->status !== LeadTaskStatus::Cancelled
            && $this->due_at !== null
            && $this->due_at->isPast();
    }
}
