<?php

namespace App\Models;

use Database\Factories\LeadFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lead extends MarketingLead
{
    protected $table = 'marketing_leads';

    public function duplicateOf(): BelongsTo
    {
        return $this->belongsTo(self::class, 'duplicate_of_id');
    }

    public function duplicates(): HasMany
    {
        return $this->hasMany(self::class, 'duplicate_of_id');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(LeadActivity::class, 'marketing_lead_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(LeadTask::class, 'marketing_lead_id');
    }

    protected static function newFactory(): Factory
    {
        return LeadFactory::new();
    }
}
