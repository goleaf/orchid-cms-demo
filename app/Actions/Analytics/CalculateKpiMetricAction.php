<?php

namespace App\Actions\Analytics;

use App\Actions\Analytics\Concerns\ReadsAnalyticsDataSafely;
use App\Enums\DocumentStatus;
use App\Enums\EnrollmentStatus;
use App\Enums\LessonStatus;
use App\Enums\PaymentStatus;
use App\Models\DrivingLesson;
use App\Models\Enrollment;
use App\Models\ExamSession;
use App\Models\KpiMetric;
use App\Models\MarketingLead;
use App\Models\Payment;
use App\Models\StudentDocument;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use InvalidArgumentException;

class CalculateKpiMetricAction
{
    use ReadsAnalyticsDataSafely;

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function handle(KpiMetric|int|string $metric, array $filters = [], ?User $user = null): array
    {
        $this->authorizeAnalyticsAccess($user, 'analytics.kpis.manage');

        $metric = $this->resolveMetric($metric);

        if (! $metric->is_active) {
            throw new InvalidArgumentException(tkey('analytics.validation.inactive_metric'));
        }

        $filters = app(ResolveAnalyticsFiltersAction::class)->handle($filters, $user);
        $value = $this->calculateValue($metric->code, $filters);

        return [
            'metric' => $metric,
            'code' => $metric->code,
            'value' => $value,
            'unit' => $metric->unit,
            'filters' => $filters,
            'calculated_at' => now()->toISOString(),
        ];
    }

    private function resolveMetric(KpiMetric|int|string $metric): KpiMetric
    {
        if ($metric instanceof KpiMetric) {
            return $metric;
        }

        if (! $this->analyticsTableExists(KpiMetric::class)) {
            throw new InvalidArgumentException(tkey('analytics.validation.inactive_metric'));
        }

        return is_numeric($metric)
            ? KpiMetric::query()->findOrFail((int) $metric)
            : KpiMetric::query()->where('code', (string) $metric)->firstOrFail();
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function calculateValue(string $code, array $filters): float
    {
        return (float) match ($code) {
            'open_leads' => $this->analyticsCount(
                MarketingLead::class,
                fn (Builder $query): Builder => $this->applyAnalyticsFilters($query->open(), $filters),
            ),
            'converted_leads' => $this->analyticsCount(
                MarketingLead::class,
                fn (Builder $query): Builder => $this->applyAnalyticsFilters($query->converted(), $filters),
            ),
            'active_students' => $this->analyticsCount(
                StudentProfile::class,
                fn (Builder $query): Builder => $this->applyAnalyticsFilters($query->active(), $filters),
            ),
            'active_enrollments' => $this->analyticsCount(
                Enrollment::class,
                fn (Builder $query): Builder => $this->applyAnalyticsFilters($query->where('status', EnrollmentStatus::Active->value), $filters),
            ),
            'lessons_today' => $this->analyticsCount(
                DrivingLesson::class,
                fn (Builder $query): Builder => $this->applyAnalyticsDimensions($query, $filters)
                    ->where('status', LessonStatus::Scheduled->value)
                    ->whereBetween('starts_at', [now()->startOfDay(), now()->endOfDay()]),
            ),
            'scheduled_exams' => $this->analyticsCount(
                ExamSession::class,
                fn (Builder $query): Builder => $this->applyAnalyticsDimensions($query->upcoming(), $filters),
            ),
            'paid_revenue_eur' => round($this->analyticsSum(
                Payment::class,
                'amount_cents',
                fn (Builder $query): Builder => $this->applyAnalyticsFilters($query->where('status', PaymentStatus::Paid->value), $filters, 'paid_at'),
            ) / 100, 2),
            'pending_documents' => $this->analyticsCount(
                StudentDocument::class,
                fn (Builder $query): Builder => $this->applyAnalyticsFilters($query, $filters)
                    ->whereIn('status', [DocumentStatus::Missing->value, DocumentStatus::Submitted->value]),
            ),
            'lead_conversion_rate_percent' => $this->leadConversionRate($filters),
            'lesson_completion_rate_percent' => $this->lessonCompletionRate($filters),
            default => 0,
        };
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function leadConversionRate(array $filters): float
    {
        $total = $this->analyticsCount(
            MarketingLead::class,
            fn (Builder $query): Builder => $this->applyAnalyticsFilters($query, $filters),
        );

        if ($total === 0) {
            return 0.0;
        }

        $converted = $this->analyticsCount(
            MarketingLead::class,
            fn (Builder $query): Builder => $this->applyAnalyticsFilters($query->converted(), $filters),
        );

        return round(($converted / $total) * 100, 2);
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function lessonCompletionRate(array $filters): float
    {
        $total = $this->analyticsCount(
            DrivingLesson::class,
            fn (Builder $query): Builder => $this->applyAnalyticsFilters($query, $filters),
        );

        if ($total === 0) {
            return 0.0;
        }

        $completed = $this->analyticsCount(
            DrivingLesson::class,
            fn (Builder $query): Builder => $this->applyAnalyticsFilters($query->where('status', LessonStatus::Completed->value), $filters),
        );

        return round(($completed / $total) * 100, 2);
    }
}
