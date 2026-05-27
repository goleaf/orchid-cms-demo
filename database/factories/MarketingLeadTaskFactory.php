<?php

namespace Database\Factories;

use App\Enums\LeadTaskPriority;
use App\Enums\LeadTaskStatus;
use App\Models\MarketingLead;
use App\Models\MarketingLeadTask;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MarketingLeadTask>
 */
class MarketingLeadTaskFactory extends Factory
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
            'assigned_to_user_id' => null,
            'created_by_user_id' => null,
            'title' => $this->faker->sentence(5),
            'status' => LeadTaskStatus::Open,
            'priority' => LeadTaskPriority::Normal,
            'due_at' => now()->addHours($this->faker->numberBetween(1, 48)),
            'completed_at' => null,
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}
