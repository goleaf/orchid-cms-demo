<?php

namespace App\Actions\Analytics;

use App\Actions\Analytics\Concerns\ReadsAnalyticsDataSafely;
use App\Enums\KpiDirection;
use App\Enums\KpiSnapshotStatus;
use App\Models\KpiTarget;

class CompareKpiTargetAction
{
    use ReadsAnalyticsDataSafely;

    /**
     * @return array<string, mixed>
     */
    public function handle(float|int|string $value, ?KpiTarget $target = null): array
    {
        $value = (float) $value;

        if ($target === null) {
            return [
                'status' => KpiSnapshotStatus::Unknown,
                'target_value' => null,
                'delta' => null,
                'percent_of_target' => null,
            ];
        }

        $targetValue = (float) $target->target_value;
        $warning = $target->warning_threshold ?? $target->warning_value;
        $success = $target->success_threshold;
        $direction = $this->analyticsEnumValue($target->direction) ?? KpiDirection::Increase->value;
        $status = match ($direction) {
            KpiDirection::Decrease->value => $this->compareDecrease($value, $targetValue, $warning, $success),
            KpiDirection::Maintain->value => $this->compareMaintain($value, $targetValue, $warning, $success),
            default => $this->compareIncrease($value, $targetValue, $warning, $success),
        };

        return [
            'status' => $status,
            'target_value' => $targetValue,
            'delta' => round($value - $targetValue, 4),
            'percent_of_target' => $targetValue == 0.0 ? null : round(($value / $targetValue) * 100, 2),
        ];
    }

    private function compareIncrease(float $value, float $target, mixed $warning, mixed $success): KpiSnapshotStatus
    {
        if ($success !== null && $value >= (float) $success) {
            return KpiSnapshotStatus::Exceeded;
        }

        if ($value >= $target) {
            return KpiSnapshotStatus::Achieved;
        }

        if ($warning !== null && $value >= (float) $warning) {
            return KpiSnapshotStatus::OnTrack;
        }

        return KpiSnapshotStatus::BelowTarget;
    }

    private function compareDecrease(float $value, float $target, mixed $warning, mixed $success): KpiSnapshotStatus
    {
        if ($success !== null && $value <= (float) $success) {
            return KpiSnapshotStatus::Exceeded;
        }

        if ($value <= $target) {
            return KpiSnapshotStatus::Achieved;
        }

        if ($warning !== null && $value <= (float) $warning) {
            return KpiSnapshotStatus::OnTrack;
        }

        return KpiSnapshotStatus::BelowTarget;
    }

    private function compareMaintain(float $value, float $target, mixed $warning, mixed $success): KpiSnapshotStatus
    {
        $distance = abs($value - $target);

        if ($success !== null && $distance <= (float) $success) {
            return KpiSnapshotStatus::Achieved;
        }

        if ($warning !== null && $distance <= (float) $warning) {
            return KpiSnapshotStatus::OnTrack;
        }

        return $distance <= 0.0001
            ? KpiSnapshotStatus::Achieved
            : KpiSnapshotStatus::BelowTarget;
    }
}
