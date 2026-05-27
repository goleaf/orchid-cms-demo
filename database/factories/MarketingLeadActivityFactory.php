<?php

namespace Database\Factories;

use App\Models\MarketingLead;
use App\Models\MarketingLeadActivity;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MarketingLeadActivity>
 */
class MarketingLeadActivityFactory extends Factory
{
    public function definition(): array
    {
        return [
            'marketing_lead_id' => MarketingLead::factory(),
            'user_id' => null,
            'type' => 'created',
            'title' => tkey('crm.activities.titles.created'),
            'body' => $this->faker->sentence(),
            'old_value' => null,
            'new_value' => null,
            'meta' => null,
        ];
    }
}
