<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketingLeadActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'marketing_lead_id',
        'user_id',
        'type',
        'title',
        'body',
        'old_value',
        'new_value',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function marketingLead(): BelongsTo
    {
        return $this->belongsTo(MarketingLead::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function typeLabel(): string
    {
        return tkey('crm.activities.types.'.$this->type);
    }
}
