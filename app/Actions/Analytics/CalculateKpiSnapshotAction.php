<?php

namespace App\Actions\Analytics;

use App\Actions\Analytics\Concerns\ReadsAnalyticsDataSafely;
use App\Enums\KpiPeriod;
use App\Models\KpiMetric;
use App\Models\KpiSnapshot;
use App\Models\KpiTarget;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class CalculateKpiSnapshotAction
{
    use ReadsAnalyticsDataSafely;

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function handle(KpiMetric|int|string $metric, KpiPeriod|string $period = KpiPeriod::Month, array $filters = [], ?User $user = null): array
    {
        $this->authorizeAnalyticsAccess($user, 'analytics.kpis.manage');

        $range = app(BuildAnalyticsDateRangeAction::class)->handle($filters, $period);
        $filters = array_merge($filters, [
            'period_type' => $range['period_type'],
            'period_start' => $range['period_start']->toDateString(),
            'period_end' => $range['period_end']->toDateString(),
        ]);

        $calculation = app(CalculateKpiMetricAction::class)->handle($metric, $filters);
        /** @var KpiMetric $metric */
        $metric = $calculation['metric'];
        $target = $this->targetFor($metric, $filters);
        $comparison = app(CompareKpiTargetAction::class)->handle($calculation['value'], $target);

        $snapshot = KpiSnapshot::query()->updateOrCreate(
            $this->analyticsExistingAttributes(KpiSnapshot::class, [
                'kpi_metric_id' => $metric->id,
                'branch_id' => $filters['branch_id'] ?? null,
                'user_id' => $filters['user_id'] ?? null,
                'period_type' => $filters['period_type'],
                'period' => $filters['period_type'],
                'period_start' => $filters['period_start'],
                'snapshot_date' => $filters['period_start'],
            ]),
            $this->analyticsExistingAttributes(KpiSnapshot::class, [
                'period_end' => $filters['period_end'],
                'value' => $calculation['value'],
                'target_value' => $comparison['target_value'],
                'status' => $comparison['status'],
                'calculated_at' => now(),
                'metadata' => [
                    'filters' => $calculation['filters'],
                    'comparison' => [
                        'delta' => $comparison['delta'],
                        'percent_of_target' => $comparison['percent_of_target'],
                    ],
                ],
                'source_payload' => ['calculation' => $calculation],
            ]),
        );

        return [
            'snapshot' => $snapshot,
            'calculation' => $calculation,
            'comparison' => $comparison,
            'target' => $target,
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function targetFor(KpiMetric $metric, array $filters): ?KpiTarget
    {
        if (! $this->analyticsTableExists(KpiTarget::class)) {
            return null;
        }

        $query = KpiTarget::query()->where('kpi_metric_id', $metric->id);
        $periodColumn = $this->analyticsColumnExists(KpiTarget::class, 'period_type') ? 'period_type' : 'period';
        $startColumn = $this->analyticsColumnExists(KpiTarget::class, 'period_start') ? 'period_start' : 'starts_on';
        $endColumn = $this->analyticsColumnExists(KpiTarget::class, 'period_end') ? 'period_end' : 'ends_on';

        $query->where($periodColumn, $filters['period_type']);

        if (filled($filters['branch_id'] ?? null) && $this->analyticsColumnExists(KpiTarget::class, 'branch_id')) {
            $query->where('branch_id', (int) $filters['branch_id']);
        }

        if (filled($filters['user_id'] ?? null) && $this->analyticsColumnExists(KpiTarget::class, 'user_id')) {
            $query->where('user_id', (int) $filters['user_id']);
        }

        $query
            ->where($startColumn, '<=', $filters['period_start'])
            ->where(function (Builder $query) use ($endColumn, $filters): void {
                $query->whereNull($endColumn)
                    ->orWhere($endColumn, '>=', $filters['period_start']);
            })
            ->orderByDesc($startColumn)
            ->orderByDesc('id');

        $target = $query->first();

        if ($target instanceof KpiTarget) {
            return $target;
        }

        $fallback = KpiTarget::query()
            ->where('kpi_metric_id', $metric->id)
            ->where($periodColumn, $filters['period_type']);

        if (filled($filters['branch_id'] ?? null) && $this->analyticsColumnExists(KpiTarget::class, 'branch_id')) {
            $fallback->where('branch_id', (int) $filters['branch_id']);
        }

        if (filled($filters['user_id'] ?? null) && $this->analyticsColumnExists(KpiTarget::class, 'user_id')) {
            $fallback->where('user_id', (int) $filters['user_id']);
        }

        return $fallback->orderByDesc('id')->first();
    }
}
