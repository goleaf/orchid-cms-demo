<?php

namespace App\Models;

use App\Enums\LeadStatus;
use Database\Factories\MarketingLeadStatusHistoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketingLeadStatusHistory extends Model
{
    /** @use HasFactory<MarketingLeadStatusHistoryFactory> */
    use HasFactory;

    protected $fillable = [
        'marketing_lead_id',
        'user_id',
        'from_status',
        'to_status',
        'reason',
        'changed_at',
    ];

    protected $casts = [
        'from_status' => LeadStatus::class,
        'to_status' => LeadStatus::class,
        'changed_at' => 'datetime',
    ];

    public function marketingLead(): BelongsTo
    {
        return $this->belongsTo(MarketingLead::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
