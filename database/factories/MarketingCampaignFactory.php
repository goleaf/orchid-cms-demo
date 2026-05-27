<?php

namespace Database\Factories;

use App\Enums\CampaignStatus;
use App\Models\Branch;
use App\Models\MarketingCampaign;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MarketingCampaign>
 */
class MarketingCampaignFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'branch_id' => Branch::factory(),
            'name' => $this->faker->randomElement(['Spring intake', 'Summer intensive', 'Evening courses']),
            'channel' => $this->faker->randomElement(['google_ads', 'facebook', 'organic', 'referral']),
            'status' => CampaignStatus::Active,
            'budget_cents' => $this->faker->numberBetween(25000, 250000),
            'starts_on' => now()->subDays($this->faker->numberBetween(1, 30)),
            'ends_on' => now()->addDays($this->faker->numberBetween(10, 60)),
            'utm_source' => $this->faker->randomElement(['google', 'facebook', 'newsletter']),
            'utm_campaign' => $this->faker->slug(3),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}
