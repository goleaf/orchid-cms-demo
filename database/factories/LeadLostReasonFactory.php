<?php

namespace Database\Factories;

use App\Models\LeadLostReason;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LeadLostReason>
 */
class LeadLostReasonFactory extends Factory
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
            'description_translations' => null,
            'color' => '#dc2626',
            'is_system' => false,
            'is_active' => true,
            'sort_order' => 0,
        ];
    }
}
