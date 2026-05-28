<?php

namespace Database\Factories;

use App\Enums\CommunicationDirection;
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
            'marketing_message_template_id' => null,
            'channel' => $this->faker->randomElement(['phone', 'sms', 'email', 'whatsapp', 'telegram', 'viber']),
            'direction' => $this->faker->randomElement([
                CommunicationDirection::Inbound->value,
                CommunicationDirection::Outbound->value,
            ]),
            'subject' => $this->faker->optional()->sentence(4),
            'body' => $this->faker->sentence(16),
            'communicated_at' => now()->subHours($this->faker->numberBetween(1, 72)),
            'client_replied_at' => null,
            'callback_required_at' => null,
            'call_recording_url' => null,
            'call_recording_reference' => null,
            'metadata' => null,
        ];
    }
}
