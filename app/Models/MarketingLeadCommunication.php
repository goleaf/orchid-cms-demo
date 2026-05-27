<?php

namespace App\Models;

use Database\Factories\MarketingLeadCommunicationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketingLeadCommunication extends Model
{
    /** @use HasFactory<MarketingLeadCommunicationFactory> */
    use HasFactory;

    protected $fillable = [
        'marketing_lead_id',
        'user_id',
        'channel',
        'direction',
        'subject',
        'body',
        'communicated_at',
        'metadata',
    ];

    protected $casts = [
        'communicated_at' => 'datetime',
        'metadata' => 'array',
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
