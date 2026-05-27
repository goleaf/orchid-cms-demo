<?php

namespace Database\Factories;

use App\Enums\StudentStatus;
use App\Models\Branch;
use App\Models\StudentProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

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
        return [
            'branch_id' => Branch::factory(),
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'date_of_birth' => $this->faker->dateTimeBetween('-45 years', '-18 years'),
            'national_id' => $this->faker->optional()->unique()->numerify('###########'),
            'address' => $this->faker->address(),
            'source' => $this->faker->randomElement(['website', 'referral', 'walk-in', 'campaign']),
            'status' => StudentStatus::Lead,
            'notes' => $this->faker->optional()->sentence(),
            'registered_at' => now()->subDays($this->faker->numberBetween(1, 60)),
        ];
    }
}
