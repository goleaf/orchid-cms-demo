<?php

namespace Database\Factories;

use App\Enums\StudentStatus;
use App\Models\Branch;
use App\Models\StudentProfile;
use App\Models\StudentStatus as StudentStatusModel;
use App\Support\Crm\PhoneNormalizer;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<StudentProfile>
 */
class StudentProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $firstName = $this->faker->firstName();
        $lastName = $this->faker->lastName();
        $phone = $this->faker->phoneNumber();

        return [
            'uuid' => (string) Str::uuid(),
            'student_number' => 'STU-'.$this->faker->unique()->numerify('2026-####'),
            'branch_id' => Branch::factory(),
            'full_name' => $firstName.' '.$lastName,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $phone,
            'normalized_phone' => PhoneNormalizer::normalize($phone),
            'date_of_birth' => $this->faker->dateTimeBetween('-45 years', '-18 years'),
            'national_id' => $this->faker->unique()->numerify('###########'),
            'address' => $this->faker->address(),
            'city' => $this->faker->city(),
            'locale' => 'en',
            'source' => $this->faker->randomElement(['website', 'referral', 'walk-in', 'campaign']),
            'status' => StudentStatus::Lead,
            'status_id' => fn (): int => $this->statusId('lead', 'lead'),
            'consent_accepted' => true,
            'consent_accepted_at' => now(),
            'notes' => $this->faker->optional()->sentence(),
            'registered_at' => now()->subDays($this->faker->numberBetween(1, 60)),
        ];
    }

    private function statusId(string $code, string $state): int
    {
        $existing = StudentStatusModel::query()->where('code', $code)->value('id');

        if ($existing !== null) {
            return (int) $existing;
        }

        return StudentStatusModel::factory()->{$state}()->create()->getKey();
    }
}
