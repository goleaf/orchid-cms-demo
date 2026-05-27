<?php

namespace App\Models;

use Database\Factories\LeadFactory;
use Illuminate\Database\Eloquent\Factories\Factory;

class Lead extends MarketingLead
{
    protected $table = 'marketing_leads';

    protected static function newFactory(): Factory
    {
        return LeadFactory::new();
    }
}
