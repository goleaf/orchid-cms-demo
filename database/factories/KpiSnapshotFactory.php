<?php

namespace Database\Factories;

use App\Enums\KpiPeriod;
use App\Enums\KpiSnapshotStatus;
use App\Models\Branch;
use App\Models\KpiMetric;
use App\Models\KpiSnapshot;
use App\Models\TrainingGroup;
use App\Models\TrainingProgram;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<KpiSnapshot>
 */
class KpiSnapshotFactory extends Factory
{
    protected $model = KpiSnapshot::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $value = $this->faker->numberBetween(10, 100);

        return [
            'uuid' => (string) Str::uuid(),
            'kpi_metric_id' => KpiMetric::factory(),
            'branch_id' => null,
            'user_id' => null,
            'period_type' => KpiPeriod::Day,
            'period_start' => now()->toDateString(),
            'period_end' => now()->toDateString(),
            'value' => $value,
            'target_value' => $value,
            'status' => KpiSnapshotStatus::OnTrack,
            'calculated_at' => now(),
            'metadata' => [],
            'period' => KpiPeriod::Day,
            'snapshot_date' => now()->toDateString(),
            'training_program_id' => null,
            'training_group_id' => null,
            'source_payload' => [],
        ];
    }

    public function forMetric(KpiMetric $metric): static
    {
        return $this->state(fn (): array => [
            'kpi_metric_id' => $metric->id,
        ]);
    }

    public function forUser(User $user): static
    {
        return $this->state(fn (): array => [
            'user_id' => $user->id,
        ]);
    }

    public function period(KpiPeriod|string $period, ?string $start = null, ?string $end = null): static
    {
        $value = $period instanceof KpiPeriod ? $period->value : $period;

        return $this->state(fn (): array => [
            'period_type' => $value,
            'period' => $value,
            'period_start' => $start ?? now()->toDateString(),
            'period_end' => $end ?? ($start ?? now()->toDateString()),
            'snapshot_date' => $start ?? now()->toDateString(),
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
}
