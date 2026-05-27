<?php

namespace Database\Factories;

use App\Enums\VehicleStatus;
use App\Models\Branch;
use App\Models\Instructor;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Vehicle>
 */
class VehicleFactory extends Factory
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
            'instructor_id' => Instructor::factory(),
            'registration_number' => strtoupper($this->faker->unique()->bothify('??-####')),
            'make' => $this->faker->randomElement(['Toyota', 'Volkswagen', 'Skoda', 'Hyundai']),
            'model' => $this->faker->randomElement(['Yaris', 'Golf', 'Fabia', 'i20']),
            'year' => $this->faker->numberBetween(2018, 2026),
            'license_category' => 'B',
            'transmission' => $this->faker->randomElement(['manual', 'automatic']),
            'odometer_km' => $this->faker->numberBetween(12000, 98000),
            'status' => VehicleStatus::Active,
            'next_service_at' => now()->addDays($this->faker->numberBetween(20, 120)),
            'next_inspection_at' => now()->addMonths($this->faker->numberBetween(2, 18)),
        ];
    }
}
