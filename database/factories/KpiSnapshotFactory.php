<?php

namespace Database\Factories;

use App\Enums\KpiPeriod;
use App\Enums\KpiSnapshotStatus;
use App\Models\Branch;
use App\Models\KpiMetric;
use App\Models\KpiSnapshot;
use App\Models\TrainingGroup;
use App\Models\TrainingProgram;
use Illuminate\Database\Eloquent\Factories\Factory;

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
            'kpi_metric_id' => KpiMetric::factory(),
            'period' => KpiPeriod::Day,
            'snapshot_date' => now()->toDateString(),
            'value' => $value,
            'target_value' => $value,
            'status' => KpiSnapshotStatus::OnTrack,
            'branch_id' => null,
            'training_program_id' => null,
            'training_group_id' => null,
            'source_payload' => [],
            'calculated_at' => now(),
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
}
