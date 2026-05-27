<?php

namespace Database\Factories;

use App\Enums\LeadStatus;
use App\Models\Branch;
use App\Models\MarketingCampaign;
use App\Models\MarketingLead;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MarketingLead>
 */
class MarketingLeadFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'marketing_campaign_id' => MarketingCampaign::factory(),
            'branch_id' => Branch::factory(),
            'converted_student_profile_id' => null,
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->optional()->lastName(),
            'email' => $this->faker->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'source' => $this->faker->randomElement(['website', 'facebook', 'google_ads', 'referral']),
            'status' => LeadStatus::New,
            'license_category' => 'B',
            'contacted_at' => null,
            'converted_at' => null,
            'message' => $this->faker->optional()->sentence(),
        ];
    }
}
