<?php

namespace Database\Factories;

use App\Models\MarketingLead;
use App\Models\MarketingLeadCommunication;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MarketingLeadCommunication>
 */
class MarketingLeadCommunicationFactory extends Factory
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
            'channel' => $this->faker->randomElement(['phone', 'email', 'whatsapp', 'telegram']),
            'direction' => $this->faker->randomElement(['inbound', 'outbound']),
            'subject' => $this->faker->optional()->sentence(4),
            'body' => $this->faker->sentence(16),
            'communicated_at' => now()->subHours($this->faker->numberBetween(1, 72)),
            'metadata' => null,
        ];
    }
}
