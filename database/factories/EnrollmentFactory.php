<?php

namespace Database\Factories;

use App\Enums\EnrollmentStatus;
use App\Models\Enrollment;
use App\Models\EnrollmentStatus as EnrollmentStatusModel;
use App\Models\Instructor;
use App\Models\StudentProfile;
use App\Models\TrainingProgram;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Enrollment>
 */
class EnrollmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startedAt = now()->subDays($this->faker->numberBetween(1, 45));

        return [
            'uuid' => (string) Str::uuid(),
            'enrollment_number' => 'ENR-'.$this->faker->unique()->numerify('2026-####'),
            'student_profile_id' => StudentProfile::factory(),
            'training_program_id' => TrainingProgram::factory(),
            'instructor_id' => Instructor::factory(),
            'status' => EnrollmentStatus::Active,
            'status_id' => fn (): int => $this->statusId('active', 'active'),
            'started_at' => $startedAt,
            'start_date' => $startedAt->toDateString(),
            'completed_at' => null,
            'contracted_price_cents' => 120000,
            'paid_cents' => $this->faker->numberBetween(20000, 120000),
            'price' => 1200.00,
            'discount' => 0,
            'currency' => 'EUR',
            'payment_status' => 'partial',
        ];
    }

    private function statusId(string $code, string $state): int
    {
        $existing = EnrollmentStatusModel::query()->where('code', $code)->value('id');

        if ($existing !== null) {
            return (int) $existing;
        }

        return EnrollmentStatusModel::factory()->{$state}()->create()->getKey();
    }
}
