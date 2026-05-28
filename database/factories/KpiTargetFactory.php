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
use Illuminate\Support\Str;

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
            'uuid' => (string) Str::uuid(),
            'kpi_metric_id' => KpiMetric::factory(),
            'branch_id' => null,
            'user_id' => null,
            'period_type' => KpiPeriod::Month,
            'period_start' => now()->startOfMonth()->toDateString(),
            'period_end' => null,
            'target_value' => $target,
            'warning_threshold' => max(0, $target - 10),
            'success_threshold' => $target + 10,
            'period' => KpiPeriod::Month,
            'starts_on' => now()->startOfMonth()->toDateString(),
            'ends_on' => null,
            'warning_value' => max(0, $target - 10),
            'direction' => KpiDirection::Increase,
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

    public function period(KpiPeriod|string $period, ?string $start = null, ?string $end = null): static
    {
        $value = $period instanceof KpiPeriod ? $period->value : $period;

        return $this->state(fn (): array => [
            'period_type' => $value,
            'period' => $value,
            'period_start' => $start ?? now()->startOfMonth()->toDateString(),
            'starts_on' => $start ?? now()->startOfMonth()->toDateString(),
            'period_end' => $end,
            'ends_on' => $end,
        ]);
    }

    public function assignedTo(User $user): static
    {
        return $this->state(fn (): array => [
            'user_id' => $user->id,
            'assigned_to_user_id' => $user->id,
            'created_by_id' => $user->id,
            'updated_by_id' => $user->id,
        ]);
    }
}
