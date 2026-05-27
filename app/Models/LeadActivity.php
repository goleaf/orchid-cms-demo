<?php

namespace App\Models;

use Database\Factories\LeadActivityFactory;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeadActivity extends MarketingLeadActivity
{
    protected $table = 'marketing_lead_activities';

    protected static function newFactory(): Factory
    {
        return LeadActivityFactory::new();
    }
}
