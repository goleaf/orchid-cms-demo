<?php

namespace App\Actions\Analytics;

use App\Enums\KpiPeriod;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use InvalidArgumentException;

class BuildAnalyticsDateRangeAction
{
    /**
     * @param  array<string, mixed>  $filters
     * @return array{period_type: string, period_start: CarbonImmutable, period_end: CarbonImmutable}
     */
    public function handle(array $filters = [], KpiPeriod|string|null $periodType = null, CarbonInterface|string|null $anchor = null): array
    {
        $period = $this->resolvePeriod($periodType ?? ($filters['period_type'] ?? $filters['period'] ?? KpiPeriod::Month->value));
        $anchorDate = $this->resolveAnchor($anchor ?? ($filters['date'] ?? null));

        if (filled($filters['period_start'] ?? null) || filled($filters['period_end'] ?? null)) {
            $start = filled($filters['period_start'] ?? null)
                ? CarbonImmutable::parse((string) $filters['period_start'])->startOfDay()
                : $anchorDate->startOfDay();

            $end = filled($filters['period_end'] ?? null)
                ? CarbonImmutable::parse((string) $filters['period_end'])->endOfDay()
                : $start->endOfDay();

            if ($end->lt($start)) {
                throw new InvalidArgumentException(tkey('analytics.validation.invalid_date_range'));
            }

            return [
                'period_type' => $period,
                'period_start' => $start,
                'period_end' => $end,
            ];
        }

        [$start, $end] = match ($period) {
            KpiPeriod::Day->value => [$anchorDate->startOfDay(), $anchorDate->endOfDay()],
            KpiPeriod::Week->value => [$anchorDate->startOfWeek()->startOfDay(), $anchorDate->endOfWeek()->endOfDay()],
            KpiPeriod::Quarter->value => [$anchorDate->startOfQuarter()->startOfDay(), $anchorDate->endOfQuarter()->endOfDay()],
            KpiPeriod::Year->value => [$anchorDate->startOfYear()->startOfDay(), $anchorDate->endOfYear()->endOfDay()],
            KpiPeriod::Custom->value => [$anchorDate->startOfDay(), $anchorDate->endOfDay()],
            default => [$anchorDate->startOfMonth()->startOfDay(), $anchorDate->endOfMonth()->endOfDay()],
        };

        return [
            'period_type' => $period,
            'period_start' => $start,
            'period_end' => $end,
        ];
    }

    private function resolvePeriod(KpiPeriod|string $period): string
    {
        $value = $period instanceof KpiPeriod ? $period->value : $period;

        return in_array($value, KpiPeriod::values(), true)
            ? $value
            : KpiPeriod::Month->value;
    }

    private function resolveAnchor(CarbonInterface|string|null $anchor): CarbonImmutable
    {
        if ($anchor instanceof CarbonInterface) {
            return CarbonImmutable::instance($anchor);
        }

        return filled($anchor)
            ? CarbonImmutable::parse((string) $anchor)
            : CarbonImmutable::instance(now());
    }
}
