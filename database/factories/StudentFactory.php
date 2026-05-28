<?php

namespace Database\Factories;

use App\Enums\StudentStatus as StudentStatusEnum;
use App\Models\Branch;
use App\Models\Lead;
use App\Models\LeadSource;
use App\Models\Student;
use App\Models\StudentStatus as StudentStatusModel;
use App\Models\User;
use App\Support\Crm\PhoneNormalizer;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Student>
 */
class StudentFactory extends Factory
{
    protected $model = Student::class;

    public function definition(): array
    {
        $firstName = $this->faker->firstName();
        $lastName = $this->faker->lastName();
        $phone = '+370 '.$this->faker->numerify('6## #####');

        return [
            'uuid' => (string) Str::uuid(),
            'student_number' => 'STU-'.$this->faker->unique()->numerify('2026-####'),
            'branch_id' => Branch::factory(),
            'full_name' => $firstName.' '.$lastName,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'middle_name' => null,
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $phone,
            'normalized_phone' => PhoneNormalizer::normalize($phone),
            'date_of_birth' => $this->faker->dateTimeBetween('-45 years', '-18 years'),
            'national_id' => $this->faker->unique()->numerify('###########'),
            'personal_code' => $this->faker->unique()->numerify('###########'),
            'gender' => $this->faker->optional()->randomElement(['female', 'male', 'other']),
            'preferred_messenger' => $this->faker->optional()->randomElement(['phone', 'email', 'telegram', 'whatsapp']),
            'address' => $this->faker->address(),
            'city' => $this->faker->city(),
            'locale' => 'en',
            'source' => 'website',
            'status' => StudentStatusEnum::Active,
            'status_id' => fn (): int => $this->statusId('active', 'active'),
            'source_label' => 'website',
            'consent_accepted' => true,
            'consent_accepted_at' => now(),
            'comment' => $this->faker->optional()->sentence(),
            'internal_comment' => $this->faker->optional()->sentence(),
            'registered_at' => now()->subDays($this->faker->numberBetween(1, 30)),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (): array => [
            'status' => StudentStatusEnum::Active,
            'status_id' => fn (): int => $this->statusId('active', 'active'),
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => [
            'status' => StudentStatusEnum::Inactive,
            'status_id' => fn (): int => $this->statusId('inactive', 'inactive'),
        ]);
    }

    public function blocked(): static
    {
        return $this->state(fn (): array => [
            'status' => StudentStatusEnum::Blocked,
            'status_id' => fn (): int => $this->statusId('blocked', 'blocked'),
        ]);
    }

    public function archived(): static
    {
        return $this->state(fn (): array => [
            'status' => StudentStatusEnum::Archived,
            'status_id' => fn (): int => $this->statusId('archived', 'archived'),
        ]);
    }

    public function assigned(): static
    {
        return $this->state(fn (): array => [
            'manager_id' => User::factory(),
            'administrator_id' => User::factory(),
        ]);
    }

    public function fromLead(): static
    {
        return $this->state(fn (): array => [
            'source_lead_id' => Lead::factory(),
            'source' => 'website',
        ]);
    }

    public function withSource(): static
    {
        return $this->state(fn (): array => [
            'source_id' => LeadSource::factory()->website(),
        ]);
    }

    public function withoutConsent(): static
    {
        return $this->state(fn (): array => [
            'consent_accepted' => false,
            'consent_accepted_at' => null,
        ]);
    }

    public function withPortalAccess(): static
    {
        return $this->state(fn (): array => [
            'portal_access_created_at' => now(),
        ]);
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
