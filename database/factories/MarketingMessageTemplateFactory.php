<?php

namespace Database\Factories;

use App\Models\MarketingMessageTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MarketingMessageTemplate>
 */
class MarketingMessageTemplateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'channel' => $this->faker->optional()->randomElement(['sms', 'email', 'whatsapp', 'telegram', 'viber']),
            'subject' => $this->faker->optional()->sentence(4),
            'body' => $this->faker->sentence(18),
            'is_active' => true,
            'sort_order' => $this->faker->numberBetween(1, 100),
        ];
    }
}
