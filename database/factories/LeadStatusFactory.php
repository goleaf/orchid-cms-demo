<?php

namespace Database\Factories;

use App\Models\LeadStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LeadStatus>
 */
class LeadStatusFactory extends Factory
{
    public function definition(): array
    {
        $code = $this->faker->unique()->slug(2);
        $name = str($code)->replace('-', ' ')->title()->toString();

        return [
            'code' => $code,
            'name' => $name,
            'name_translations' => [
                'ru' => $name,
                'en' => $name,
                'lt' => $name,
                'pl' => $name,
            ],
            'color' => '#2563eb',
            'is_system' => false,
            'is_active' => true,
            'is_default' => false,
            'is_final' => false,
            'is_success' => false,
            'is_lost' => false,
            'sort_order' => 0,
        ];
    }
}
