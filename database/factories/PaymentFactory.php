<?php

namespace Database\Factories;

use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\StudentProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'student_profile_id' => StudentProfile::factory(),
            'enrollment_id' => null,
            'amount_cents' => $this->faker->numberBetween(5000, 50000),
            'currency' => 'EUR',
            'method' => $this->faker->randomElement(['cash', 'card', 'bank_transfer']),
            'status' => PaymentStatus::Paid,
            'paid_at' => now()->subDays($this->faker->numberBetween(1, 30)),
            'reference' => 'PAY-'.$this->faker->unique()->numerify('######'),
            'notes' => null,
        ];
    }
}
