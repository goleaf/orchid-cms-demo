<?php

namespace App\Models;

use Database\Factories\LeadTaskFactory;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeadTask extends MarketingLeadTask
{
    protected $table = 'marketing_lead_tasks';

    protected static function newFactory(): Factory
    {
        return LeadTaskFactory::new();
    }
}
