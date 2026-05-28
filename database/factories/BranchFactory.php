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
    protected $model = Branch::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = 'DrivePro Academy Training Branch';
        $city = 'Vilnius';
        $address = 'Gedimino Ave. 1';
        $description = 'Driving school branch for consultations, theory lessons, and student support.';

        return [
            'uuid' => (string) Str::uuid(),
            'code' => strtoupper($this->faker->unique()->bothify('BR-####')),
            'name' => $name,
            'name_translations' => $this->translations('Филиал DrivePro Academy', $name, 'DrivePro Academy filialas', 'Oddzial DrivePro Academy'),
            'slug' => $this->faker->unique()->slug(2),
            'city' => $city,
            'city_translations' => $this->translations('Вильнюс', $city, 'Vilniaus miestas', 'Wilno'),
            'address' => $address,
            'address_translations' => $this->translations('Gedimino pr. 1', $address, 'Gedimino pr. 1', 'Gedimino pr. 1'),
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->companyEmail(),
            'description' => $description,
            'description_translations' => $this->translations(
                'Филиал автошколы для консультаций, теории и поддержки учеников.',
                $description,
                'Vairavimo mokyklos filialas konsultacijoms, teorijai ir mokiniu pagalbai.',
                'Oddzial szkoly jazdy do konsultacji, teorii i wsparcia uczniow.'
            ),
            'working_hours' => 'Mon-Fri 09:00-18:00',
            'working_hours_translations' => $this->translations('Пн-Пт 09:00-18:00', 'Mon-Fri 09:00-18:00', 'Pr-Pn 09:00-18:00', 'Pn-Pt 09:00-18:00'),
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
            'is_indexable' => true,
            'sort_order' => 0,
            'created_by_id' => null,
            'updated_by_id' => null,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (): array => ['is_active' => true]);
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => ['is_active' => false]);
    }

    public function visibleOnSite(): static
    {
        return $this->state(fn (): array => ['is_visible_on_site' => true]);
    }

    public function hiddenFromSite(): static
    {
        return $this->state(fn (): array => ['is_visible_on_site' => false]);
    }

    public function translated(): static
    {
        return $this->state(fn (): array => [
            'name' => 'DrivePro Academy Vilnius',
            'name_translations' => $this->translations('DrivePro Academy Вильнюс', 'DrivePro Academy Vilnius', 'DrivePro Academy Vilniaus filialas', 'DrivePro Academy Wilno'),
            'city' => 'Vilnius',
            'city_translations' => $this->translations('Вильнюс', 'Vilnius', 'Vilniaus miestas', 'Wilno'),
            'address_translations' => $this->translations('Gedimino pr. 1', 'Gedimino Ave. 1', 'Gedimino pr. 1', 'Gedimino pr. 1'),
            'description_translations' => $this->translations('Филиал автошколы для консультаций и занятий.', 'Driving school branch for consultations and lessons.', 'Vairavimo mokyklos filialas konsultacijoms ir pamokoms.', 'Oddzial szkoly jazdy do konsultacji i zajec.'),
        ]);
    }

    public function withCoordinates(float $latitude = 54.6872, float $longitude = 25.2797): static
    {
        return $this->state(fn (): array => [
            'latitude' => $latitude,
            'longitude' => $longitude,
            'map_url' => 'https://maps.google.com/?q='.$latitude.','.$longitude,
        ]);
    }

    public function withContacts(string $phone = '+370 600 00000', string $email = 'info@drivepro.test'): static
    {
        return $this->state(fn (): array => [
            'phone' => $phone,
            'email' => $email,
        ]);
    }

    /**
     * @param  array<string, string>  $translations
     */
    public function publicWebsite(array $translations): static
    {
        return $this->state(fn (): array => [
            'name' => $translations['name'],
            'name_translations' => $this->translations(
                $translations['name'],
                $translations['name_en'] ?? $translations['name'],
                $translations['name_lt'] ?? $translations['name_en'] ?? $translations['name'],
                $translations['name_pl'] ?? $translations['name_en'] ?? $translations['name'],
            ),
            'city' => $translations['city'],
            'city_translations' => $this->translations(
                $translations['city'],
                $translations['city_en'] ?? $translations['city'],
                $translations['city_lt'] ?? $translations['city_en'] ?? $translations['city'],
                $translations['city_pl'] ?? $translations['city_en'] ?? $translations['city'],
            ),
            'address' => $translations['address'],
            'address_translations' => $this->translations(
                $translations['address'],
                $translations['address_en'] ?? $translations['address'],
                $translations['address_lt'] ?? $translations['address_en'] ?? $translations['address'],
                $translations['address_pl'] ?? $translations['address_en'] ?? $translations['address'],
            ),
            'description' => $translations['description'],
            'description_translations' => $this->translations(
                $translations['description'],
                $translations['description_en'] ?? $translations['description'],
                $translations['description_lt'] ?? $translations['description_en'] ?? $translations['description'],
                $translations['description_pl'] ?? $translations['description_en'] ?? $translations['description'],
            ),
            'working_hours' => $translations['working_hours'] ?? 'Пн-Пт 09:00-18:00',
            'working_hours_translations' => $this->translations(
                $translations['working_hours'] ?? 'Пн-Пт 09:00-18:00',
                $translations['working_hours_en'] ?? 'Mon-Fri 09:00-18:00',
                $translations['working_hours_lt'] ?? 'Pr-Pn 09:00-18:00',
                $translations['working_hours_pl'] ?? 'Pn-Pt 09:00-18:00',
            ),
            'seo_title' => $translations['seo_title'] ?? $translations['name'],
            'seo_title_translations' => $this->translations(
                $translations['seo_title'] ?? $translations['name'],
                $translations['seo_title_en'] ?? $translations['name_en'] ?? $translations['name'],
                $translations['seo_title_lt'] ?? $translations['name_lt'] ?? $translations['name_en'] ?? $translations['name'],
                $translations['seo_title_pl'] ?? $translations['name_pl'] ?? $translations['name_en'] ?? $translations['name'],
            ),
            'seo_description' => $translations['seo_description'] ?? $translations['description'],
            'seo_description_translations' => $this->translations(
                $translations['seo_description'] ?? $translations['description'],
                $translations['seo_description_en'] ?? $translations['description_en'] ?? $translations['description'],
                $translations['seo_description_lt'] ?? $translations['description_lt'] ?? $translations['description_en'] ?? $translations['description'],
                $translations['seo_description_pl'] ?? $translations['description_pl'] ?? $translations['description_en'] ?? $translations['description'],
            ),
        ]);
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
