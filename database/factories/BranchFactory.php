<?php

namespace Database\Factories;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Branch>
 */
class BranchFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'code' => strtoupper($this->faker->unique()->bothify('BR-####')),
            'name' => $this->faker->company().' Driving School',
            'slug' => $this->faker->unique()->slug(2),
            'city' => $this->faker->city(),
            'address' => $this->faker->streetAddress(),
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->companyEmail(),
            'description' => $this->faker->paragraph(),
            'working_hours' => 'Mon-Fri 09:00-18:00',
            'latitude' => null,
            'longitude' => null,
            'map_url' => null,
            'image' => null,
            'seo_title' => null,
            'seo_description' => null,
            'canonical_url' => null,
            'open_graph_image' => null,
            'is_active' => true,
            'is_visible_on_site' => true,
            'sort_order' => 0,
            'created_by_id' => null,
            'updated_by_id' => null,
        ];
    }

    /**
     * @param  array<string, string>  $translations
     */
    public function publicWebsite(array $translations): static
    {
        return $this->state(fn (): array => [
            'name' => $translations['name'],
            'name_translations' => [
                'ru' => $translations['name'],
                'en' => $translations['name_en'] ?? $translations['name'],
            ],
            'city' => $translations['city'],
            'city_translations' => [
                'ru' => $translations['city'],
                'en' => $translations['city_en'] ?? $translations['city'],
            ],
            'address' => $translations['address'],
            'address_translations' => [
                'ru' => $translations['address'],
                'en' => $translations['address_en'] ?? $translations['address'],
            ],
            'description' => $translations['description'],
            'description_translations' => [
                'ru' => $translations['description'],
                'en' => $translations['description_en'] ?? $translations['description'],
            ],
            'working_hours' => $translations['working_hours'] ?? 'Пн-Пт 09:00-18:00',
            'working_hours_translations' => [
                'ru' => $translations['working_hours'] ?? 'Пн-Пт 09:00-18:00',
                'en' => $translations['working_hours_en'] ?? 'Mon-Fri 09:00-18:00',
            ],
            'seo_title' => $translations['seo_title'] ?? $translations['name'],
            'seo_title_translations' => [
                'ru' => $translations['seo_title'] ?? $translations['name'],
                'en' => $translations['seo_title_en'] ?? $translations['name_en'] ?? $translations['name'],
            ],
            'seo_description' => $translations['seo_description'] ?? $translations['description'],
            'seo_description_translations' => [
                'ru' => $translations['seo_description'] ?? $translations['description'],
                'en' => $translations['seo_description_en'] ?? $translations['description_en'] ?? $translations['description'],
            ],
        ]);
    }
}
