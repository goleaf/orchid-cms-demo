<?php

namespace Database\Factories;

use App\Enums\AnalyticsSnapshotType;
use App\Enums\KpiPeriod;
use App\Models\AnalyticsSnapshot;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<AnalyticsSnapshot>
 */
class AnalyticsSnapshotFactory extends Factory
{
    protected $model = AnalyticsSnapshot::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'snapshot_type' => AnalyticsSnapshotType::OwnerDashboard->value,
            'period_type' => KpiPeriod::Day->value,
            'period_start' => now()->toDateString(),
            'period_end' => now()->toDateString(),
            'branch_id' => null,
            'user_id' => null,
            'data' => ['records' => 0],
            'calculated_at' => now(),
            'metadata' => [],
        ];
    }

    public function type(AnalyticsSnapshotType|string $type): static
    {
        $value = $type instanceof AnalyticsSnapshotType ? $type->value : $type;

        return $this->state(fn (): array => ['snapshot_type' => $value]);
    }

    public function period(KpiPeriod|string $period, ?string $start = null, ?string $end = null): static
    {
        $value = $period instanceof KpiPeriod ? $period->value : $period;

        return $this->state(fn (): array => [
            'period_type' => $value,
            'period_start' => $start ?? now()->toDateString(),
            'period_end' => $end ?? ($start ?? now()->toDateString()),
        ]);
    }

    public function scopedTo(?Branch $branch = null, ?User $user = null): static
    {
        return $this->state(fn (): array => [
            'branch_id' => $branch?->id,
            'user_id' => $user?->id,
        ]);
    }
}
