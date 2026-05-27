<?php

namespace Database\Factories;

use App\Models\SiteSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SiteSetting>
 */
class SiteSettingFactory extends Factory
{
    protected $model = SiteSetting::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'key' => $this->faker->unique()->slug(2, '_'),
            'group' => 'website',
            'value' => [
                'ru' => $this->faker->sentence(4),
                'en' => $this->faker->sentence(4),
            ],
            'description' => $this->faker->sentence(10),
            'is_public' => false,
        ];
    }
}
