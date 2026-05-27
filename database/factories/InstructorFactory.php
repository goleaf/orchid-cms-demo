<?php

namespace Database\Factories;

use App\Enums\InstructorStatus;
use App\Models\Branch;
use App\Models\Instructor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Instructor>
 */
class InstructorFactory extends Factory
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
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'license_number' => 'INS-'.$this->faker->unique()->numerify('#####'),
            'categories' => ['B'],
            'status' => InstructorStatus::Active,
            'hired_at' => $this->faker->dateTimeBetween('-5 years', '-2 months'),
        ];
    }
}
