<?php

namespace Database\Factories;

use App\Enums\LeadStatus;
use App\Models\MarketingLead;
use App\Models\MarketingLeadStatusHistory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MarketingLeadStatusHistory>
 */
class MarketingLeadStatusHistoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'marketing_lead_id' => MarketingLead::factory(),
            'user_id' => null,
            'from_status' => LeadStatus::New,
            'to_status' => LeadStatus::Contacted,
            'reason' => $this->faker->optional()->sentence(),
            'changed_at' => now()->subHours($this->faker->numberBetween(1, 72)),
        ];
    }
}
