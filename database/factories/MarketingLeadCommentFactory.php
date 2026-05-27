<?php

namespace Database\Factories;

use App\Models\MarketingLead;
use App\Models\MarketingLeadComment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MarketingLeadComment>
 */
class MarketingLeadCommentFactory extends Factory
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
            'body' => $this->faker->sentence(14),
            'is_internal' => true,
        ];
    }
}
