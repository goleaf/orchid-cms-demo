<?php

namespace App\Models;

use App\Enums\CampaignStatus;
use Database\Factories\MarketingCampaignFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MarketingCampaign extends Model
{
    /** @use HasFactory<MarketingCampaignFactory> */
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'name',
        'channel',
        'status',
        'budget_cents',
        'starts_on',
        'ends_on',
        'utm_source',
        'utm_campaign',
        'notes',
    ];

    protected $casts = [
        'status' => CampaignStatus::class,
        'budget_cents' => 'integer',
        'starts_on' => 'date',
        'ends_on' => 'date',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function leads(): HasMany
    {
        return $this->hasMany(MarketingLead::class);
    }

    public function budgetForHumans(): string
    {
        return number_format($this->budget_cents / 100, 2).' EUR';
    }

    public function scopeForCampaignList(Builder $query): Builder
    {
        return $query->select([
            'id',
            'branch_id',
            'name',
            'channel',
            'status',
            'budget_cents',
            'starts_on',
            'ends_on',
            'utm_source',
            'utm_campaign',
        ]);
    }
}
