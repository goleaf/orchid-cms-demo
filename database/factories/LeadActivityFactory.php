<?php

namespace Database\Factories;

use App\Models\Lead;
use App\Models\LeadActivity;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LeadActivity>
 */
class LeadActivityFactory extends Factory
{
    protected $model = LeadActivity::class;

    public function definition(): array
    {
        return [
            'marketing_lead_id' => Lead::factory(),
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
