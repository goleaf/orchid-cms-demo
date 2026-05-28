<?php

namespace Database\Factories;

use App\Enums\ExamSessionStatus;
use App\Enums\ExamType as ExamTypeEnum;
use App\Models\Branch;
use App\Models\ExamSession;
use App\Models\ExamStatus as ExamStatusModel;
use App\Models\ExamType as ExamTypeModel;
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
        $type = $this->faker->randomElement(ExamTypeEnum::cases());
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
            'exam_type' => ExamTypeEnum::InternalTheory,
            'provider' => 'internal',
            'vehicle_id' => null,
        ]);
    }

    public function internalPractical(): static
    {
        return $this->state(fn (): array => [
            'exam_type' => ExamTypeEnum::InternalPractical,
            'provider' => 'internal',
            'vehicle_id' => Vehicle::factory(),
        ]);
    }

    public function officialTheory(): static
    {
        return $this->state(fn (): array => [
            'exam_type' => ExamTypeEnum::StateTheory,
            'provider' => 'state',
            'vehicle_id' => null,
            'external_reference' => 'OFFICIAL-THEORY-PENDING',
            'official_placeholder_payload' => ['sync' => 'manual_placeholder'],
        ]);
    }

    public function officialPractical(): static
    {
        return $this->state(fn (): array => [
            'exam_type' => ExamTypeEnum::StatePractical,
            'provider' => 'state',
            'vehicle_id' => Vehicle::factory(),
            'external_reference' => 'OFFICIAL-PRACTICAL-PENDING',
            'official_placeholder_payload' => ['sync' => 'manual_placeholder'],
        ]);
    }

    public function statePlaceholder(): static
    {
        return $this->officialPractical();
    }

    public function scheduled(): static
    {
        return $this->state(fn (): array => [
            'status' => ExamSessionStatus::Planned,
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

    public function completed(): static
    {
        return $this->state(fn (): array => [
            'status' => ExamSessionStatus::Completed,
            'starts_at' => now()->subDays(7),
            'scheduled_at' => now()->subDays(7),
            'ends_at' => now()->subDays(7)->addHour(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (): array => [
            'status' => ExamSessionStatus::Cancelled,
        ]);
    }

    public function withGroup(?TrainingGroup $group = null): static
    {
        if ($group !== null) {
            return $this->forGroup($group);
        }

        return $this->afterCreating(function (ExamSession $session): void {
            $group = TrainingGroup::factory()->create();

            $session->forceFill([
                'branch_id' => $group->branch_id,
                'group_id' => $group->id,
                'training_group_id' => $group->id,
                'training_program_id' => $group->training_program_id,
                'instructor_id' => $group->instructor_id,
            ])->save();
        });
    }

    public function withExaminer(?User $examiner = null): static
    {
        return $this->state(fn (): array => [
            'examiner_id' => $examiner?->id ?? User::factory(),
        ]);
    }

    public function withVehicle(?Vehicle $vehicle = null): static
    {
        return $this->state(fn (): array => [
            'vehicle_id' => $vehicle?->id ?? Vehicle::factory(),
        ]);
    }

    public function translated(): static
    {
        return $this->state(fn (): array => [
            'type_id' => ExamTypeModel::factory()->translated(),
            'status_id' => ExamStatusModel::factory()->translated(),
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
