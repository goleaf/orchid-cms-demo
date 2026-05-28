<?php

namespace Database\Factories;

use App\Enums\ExamSessionStatus;
use App\Enums\ExamType;
use App\Models\Branch;
use App\Models\ExamSession;
use App\Models\Instructor;
use App\Models\TrainingGroup;
use App\Models\TrainingProgram;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ExamSession>
 */
class ExamSessionFactory extends Factory
{
    public function definition(): array
    {
        $type = $this->faker->randomElement(ExamType::cases());
        $startsAt = now()->addDays($this->faker->numberBetween(3, 30))->setTime($this->faker->numberBetween(8, 16), 0);

        return [
            'uuid' => (string) Str::uuid(),
            'exam_number' => 'EXM-'.$this->faker->unique()->numerify('######'),
            'type_id' => null,
            'status_id' => null,
            'branch_id' => Branch::factory(),
            'group_id' => null,
            'training_program_id' => TrainingProgram::factory(),
            'training_group_id' => null,
            'instructor_id' => Instructor::factory(),
            'vehicle_id' => null,
            'classroom_id' => null,
            'exam_type' => $type,
            'provider' => $type->provider(),
            'status' => ExamSessionStatus::Planned,
            'scheduled_at' => $startsAt,
            'examiner_id' => User::factory(),
            'starts_at' => $startsAt,
            'ends_at' => (clone $startsAt)->addHour(),
            'location' => $this->faker->randomElement(['Main classroom', 'Practice yard', 'State exam office']),
            'capacity' => $this->faker->numberBetween(1, 16),
            'seats_taken' => 0,
            'external_reference' => null,
            'official_placeholder_payload' => null,
            'notes' => null,
            'internal_notes' => null,
            'created_by_id' => User::factory(),
            'updated_by_id' => null,
        ];
    }

    public function internalTheory(): static
    {
        return $this->state(fn (): array => [
            'exam_type' => ExamType::InternalTheory,
            'provider' => 'internal',
            'vehicle_id' => null,
        ]);
    }

    public function internalPractical(): static
    {
        return $this->state(fn (): array => [
            'exam_type' => ExamType::InternalPractical,
            'provider' => 'internal',
            'vehicle_id' => Vehicle::factory(),
        ]);
    }

    public function statePlaceholder(): static
    {
        return $this->state(fn (): array => [
            'exam_type' => ExamType::StatePractical,
            'provider' => 'state',
            'external_reference' => 'STATE-PENDING',
            'official_placeholder_payload' => ['sync' => 'not_configured'],
        ]);
    }

    public function open(): static
    {
        return $this->state(fn (): array => [
            'status' => ExamSessionStatus::Open,
            'capacity' => 8,
            'seats_taken' => 0,
        ]);
    }

    public function forGroup(TrainingGroup $group): static
    {
        return $this->state(fn (): array => [
            'branch_id' => $group->branch_id,
            'group_id' => $group->id,
            'training_program_id' => $group->training_program_id,
            'training_group_id' => $group->id,
            'instructor_id' => $group->instructor_id,
        ]);
    }
}
