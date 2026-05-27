<?php

namespace Database\Factories;

use App\Models\LeadTag;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LeadTag>
 */
class LeadTagFactory extends Factory
{
    public function definition(): array
    {
        $slug = $this->faker->unique()->slug(2);
        $name = str($slug)->replace('-', ' ')->title()->toString();

        return [
            'slug' => $slug,
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
            'sort_order' => 0,
        ];
    }
}
