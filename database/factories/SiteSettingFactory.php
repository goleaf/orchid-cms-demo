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
            'value' => $this->translations($this->faker->sentence(4)),
            'description' => $this->faker->sentence(10),
            'is_public' => false,
        ];
    }

    public function public(): static
    {
        return $this->state(fn (): array => ['is_public' => true]);
    }

    public function private(): static
    {
        return $this->state(fn (): array => ['is_public' => false]);
    }

    public function groupWebsite(): static
    {
        return $this->state(fn (): array => ['group' => 'website']);
    }

    public function groupContacts(): static
    {
        return $this->state(fn (): array => ['group' => 'contacts']);
    }

    public function groupSeo(): static
    {
        return $this->state(fn (): array => ['group' => 'seo']);
    }

    public function groupAnalytics(): static
    {
        return $this->state(fn (): array => ['group' => 'analytics']);
    }

    /**
     * @return array<string, string>
     */
    private function translations(string $ru, ?string $en = null, ?string $lt = null, ?string $pl = null): array
    {
        return [
            'ru' => $ru,
            'en' => $en ?? $ru,
            'lt' => $lt ?? $en ?? $ru,
            'pl' => $pl ?? $en ?? $ru,
        ];
    }
}
