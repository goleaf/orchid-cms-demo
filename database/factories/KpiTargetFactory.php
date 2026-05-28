<?php

namespace Database\Factories;

use App\Enums\KpiDirection;
use App\Enums\KpiPeriod;
use App\Models\Branch;
use App\Models\KpiMetric;
use App\Models\KpiTarget;
use App\Models\TrainingGroup;
use App\Models\TrainingProgram;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<KpiTarget>
 */
class KpiTargetFactory extends Factory
{
    protected $model = KpiTarget::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $target = $this->faker->numberBetween(10, 100);

        return [
            'kpi_metric_id' => KpiMetric::factory(),
            'period' => KpiPeriod::Month,
            'starts_on' => now()->startOfMonth()->toDateString(),
            'ends_on' => null,
            'target_value' => $target,
            'warning_value' => max(0, $target - 10),
            'direction' => KpiDirection::Increase,
            'branch_id' => null,
            'training_program_id' => null,
            'training_group_id' => null,
            'assigned_to_user_id' => null,
            'metadata' => [],
            'created_by_id' => null,
            'updated_by_id' => null,
        ];
    }

    public function forMetric(KpiMetric $metric): static
    {
        return $this->state(fn (): array => [
            'kpi_metric_id' => $metric->id,
        ]);
    }

    public function scopedTo(?Branch $branch = null, ?TrainingProgram $program = null, ?TrainingGroup $group = null): static
    {
        return $this->state(fn (): array => [
            'branch_id' => $branch?->id,
            'training_program_id' => $program?->id,
            'training_group_id' => $group?->id,
        ]);
    }

    public function assignedTo(User $user): static
    {
        return $this->state(fn (): array => [
            'assigned_to_user_id' => $user->id,
            'created_by_id' => $user->id,
            'updated_by_id' => $user->id,
        ]);
    }
}
