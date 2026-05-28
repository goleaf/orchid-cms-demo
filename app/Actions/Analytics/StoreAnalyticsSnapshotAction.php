<?php

namespace App\Actions\Analytics;

use App\Enums\AnalyticsSnapshotType;
use App\Enums\KpiPeriod;
use App\Models\AnalyticsSnapshot;
use App\Models\Branch;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonInterface;

class StoreAnalyticsSnapshotAction
{
    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $metadata
     */
    public function handle(
        AnalyticsSnapshotType|string $snapshotType,
        KpiPeriod|string $periodType,
        CarbonInterface|string $periodStart,
        CarbonInterface|string|null $periodEnd,
        array $data,
        Branch|int|null $branch = null,
        User|int|null $user = null,
        array $metadata = [],
        ?CarbonInterface $calculatedAt = null,
    ): AnalyticsSnapshot {
        $calculatedAt ??= now();

        return AnalyticsSnapshot::query()->create([
            'snapshot_type' => $snapshotType instanceof AnalyticsSnapshotType ? $snapshotType->value : $snapshotType,
            'period_type' => $periodType instanceof KpiPeriod ? $periodType->value : $periodType,
            'period_start' => $this->date($periodStart),
            'period_end' => $periodEnd === null ? null : $this->date($periodEnd),
            'branch_id' => $branch instanceof Branch ? $branch->getKey() : $branch,
            'user_id' => $user instanceof User ? $user->getKey() : $user,
            'data' => $data,
            'calculated_at' => $calculatedAt,
            'metadata' => $metadata,
        ]);
    }

    private function date(CarbonInterface|string $date): string
    {
        return $date instanceof CarbonInterface
            ? $date->toDateString()
            : Carbon::parse($date)->toDateString();
    }
}
