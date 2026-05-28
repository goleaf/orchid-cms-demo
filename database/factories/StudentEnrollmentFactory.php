<?php

namespace Database\Factories;

use App\Enums\EnrollmentStatus as EnrollmentStatusEnum;
use App\Models\Branch;
use App\Models\CourseCategory;
use App\Models\EnrollmentStatus as EnrollmentStatusModel;
use App\Models\Lead;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\TrainingGroup;
use App\Models\TrainingProgram;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<StudentEnrollment>
 */
class StudentEnrollmentFactory extends Factory
{
    protected $model = StudentEnrollment::class;

    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'enrollment_number' => 'ENR-'.$this->faker->unique()->numerify('2026-####'),
            'student_profile_id' => Student::factory(),
            'training_program_id' => TrainingProgram::factory(),
            'status' => EnrollmentStatusEnum::WaitingDocuments,
            'status_id' => fn (): int => $this->statusId('waiting_documents', 'waitingDocuments'),
            'started_at' => null,
            'start_date' => null,
            'planned_end_date' => null,
            'actual_end_date' => null,
            'completed_at' => null,
            'preferred_time' => $this->faker->optional()->randomElement(['morning', 'daytime', 'evening', 'weekend']),
            'training_language' => $this->faker->optional()->randomElement(['ru', 'en', 'lt', 'pl']),
            'format' => $this->faker->optional()->randomElement(['offline', 'online', 'hybrid', 'individual', 'group']),
            'gearbox_type' => $this->faker->optional()->randomElement(['manual', 'automatic']),
            'contracted_price_cents' => 129000,
            'paid_cents' => 0,
            'price' => 1290.00,
            'discount' => 0,
            'currency' => 'EUR',
            'payment_status' => 'waiting',
            'theory_progress' => 0,
            'practice_progress' => 0,
            'total_theory_hours' => 40,
            'completed_theory_hours' => 0,
            'total_practice_hours' => 30,
            'completed_practice_hours' => 0,
            'notes' => $this->faker->optional()->sentence(),
            'internal_notes' => $this->faker->optional()->sentence(),
        ];
    }

    public function waitingDocuments(): static
    {
        return $this->state(fn (): array => [
            'status' => EnrollmentStatusEnum::WaitingDocuments,
            'status_id' => fn (): int => $this->statusId('waiting_documents', 'waitingDocuments'),
        ]);
    }

    public function waitingPayment(): static
    {
        return $this->state(fn (): array => [
            'status' => EnrollmentStatusEnum::WaitingPayment,
            'status_id' => fn (): int => $this->statusId('waiting_payment', 'waitingPayment'),
        ]);
    }

    public function waitingStart(): static
    {
        return $this->state(fn (): array => [
            'status' => EnrollmentStatusEnum::WaitingStart,
            'status_id' => fn (): int => $this->statusId('waiting_start', 'waitingStart'),
        ]);
    }

    public function active(): static
    {
        return $this->state(fn (): array => [
            'status' => EnrollmentStatusEnum::Active,
            'status_id' => fn (): int => $this->statusId('active', 'active'),
            'started_at' => now()->subDays(7),
            'start_date' => now()->subDays(7)->toDateString(),
        ]);
    }

    public function paused(): static
    {
        return $this->state(fn (): array => [
            'status' => EnrollmentStatusEnum::Paused,
            'status_id' => fn (): int => $this->statusId('paused', 'paused'),
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (): array => [
            'status' => EnrollmentStatusEnum::Completed,
            'status_id' => fn (): int => $this->statusId('completed', 'completed'),
            'completed_at' => now()->subDay(),
            'actual_end_date' => now()->subDay()->toDateString(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (): array => [
            'status' => EnrollmentStatusEnum::Cancelled,
            'status_id' => fn (): int => $this->statusId('cancelled', 'cancelled'),
        ]);
    }

    public function withLead(): static
    {
        return $this->state(fn (): array => [
            'lead_id' => Lead::factory(),
        ]);
    }

    public function withCourseCategory(): static
    {
        return $this->state(fn (): array => [
            'course_category_id' => CourseCategory::factory(),
        ]);
    }

    public function withBranch(): static
    {
        return $this->state(fn (): array => [
            'branch_id' => Branch::factory(),
        ]);
    }

    public function withTrainingGroup(): static
    {
        return $this->state(fn (): array => [
            'training_group_id' => TrainingGroup::factory(),
        ]);
    }

    public function assigned(): static
    {
        return $this->state(fn (): array => [
            'manager_id' => User::factory(),
            'administrator_id' => User::factory(),
            'teacher_id' => User::factory(),
        ]);
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
