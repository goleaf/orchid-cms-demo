<?php

namespace App\Models;

use App\Enums\LeadStatus;
use Database\Factories\MarketingLeadFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketingLead extends Model
{
    /** @use HasFactory<MarketingLeadFactory> */
    use HasFactory;

    protected $fillable = [
        'marketing_campaign_id',
        'branch_id',
        'converted_student_profile_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'source',
        'status',
        'license_category',
        'contacted_at',
        'converted_at',
        'message',
    ];

    protected $casts = [
        'status' => LeadStatus::class,
        'contacted_at' => 'datetime',
        'converted_at' => 'datetime',
    ];

    public function marketingCampaign(): BelongsTo
    {
        return $this->belongsTo(MarketingCampaign::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function convertedStudentProfile(): BelongsTo
    {
        return $this->belongsTo(StudentProfile::class, 'converted_student_profile_id');
    }

    public function fullName(): string
    {
        return trim($this->first_name.' '.($this->last_name ?? ''));
    }

    public function scopeForLeadList(Builder $query): Builder
    {
        return $query->select([
            'id',
            'marketing_campaign_id',
            'branch_id',
            'converted_student_profile_id',
            'first_name',
            'last_name',
            'email',
            'phone',
            'source',
            'status',
            'license_category',
            'contacted_at',
            'converted_at',
            'created_at',
        ]);
    }
}
