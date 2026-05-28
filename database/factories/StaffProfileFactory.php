<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\StaffProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<StaffProfile>
 */
class StaffProfileFactory extends Factory
{
    protected $model = StaffProfile::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->name();
        $jobTitle = $this->faker->jobTitle();

        return [
            'uuid' => (string) Str::uuid(),
            'user_id' => User::factory(),
            'staff_number' => 'STAFF-'.now()->format('Y').'-'.$this->faker->unique()->numerify('####'),
            'branch_id' => null,
            'display_name_translations' => $this->translations($name, $name, $name, $name),
            'job_title_translations' => $this->translations($jobTitle, $jobTitle, $jobTitle, $jobTitle),
            'public_bio_translations' => null,
            'phone' => $this->faker->phoneNumber(),
            'work_email' => $this->faker->unique()->safeEmail(),
            'preferred_locale' => 'en',
            'timezone' => config('app.timezone', 'Europe/Vilnius'),
            'avatar' => null,
            'is_visible_on_site' => false,
            'internal_notes' => null,
            'created_by_id' => null,
            'updated_by_id' => null,
        ];
    }

    public function visibleOnSite(): static
    {
        return $this->state(fn (): array => ['is_visible_on_site' => true]);
    }

    public function hiddenFromSite(): static
    {
        return $this->state(fn (): array => ['is_visible_on_site' => false]);
    }

    public function withBranch(): static
    {
        return $this->state(fn (): array => ['branch_id' => Branch::factory()]);
    }

    public function translated(): static
    {
        return $this->state(fn (): array => [
            'display_name_translations' => $this->translations('Анна Инструктор', 'Anna Instructor', 'Instruktore Anna', 'Instruktorka Anna'),
            'job_title_translations' => $this->translations('Старший инструктор', 'Senior instructor', 'Vyresnioji instruktore', 'Starszy instruktor'),
            'public_bio_translations' => $this->translations(
                'Помогает ученикам уверенно готовиться к экзамену.',
                'Helps students prepare for exams with confidence.',
                'Padeda mokiniams uztikrintai ruostis egzaminui.',
                'Pomaga uczniom pewnie przygotowac sie do egzaminu.',
            ),
        ]);
    }

    /**
     * @return array<string, string>
     */
    private function translations(string $ru, string $en, string $lt, string $pl): array
    {
        return [
            'ru' => $ru,
            'en' => $en,
            'lt' => $lt,
            'pl' => $pl,
        ];
    }
}
