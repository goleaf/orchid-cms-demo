<?php

namespace App\Actions\Analytics;

use App\Actions\Analytics\Concerns\ReadsAnalyticsDataSafely;
use App\Enums\AnalyticsRunStatus;
use App\Enums\EnrollmentStatus;
use App\Enums\ExamAdmissionStatus;
use App\Enums\ExamAttemptStatus;
use App\Enums\ExamSessionStatus;
use App\Enums\LeadStatus;
use App\Enums\LessonStatus;
use App\Enums\PaymentStatus;
use App\Models\DrivingLesson;
use App\Models\Enrollment;
use App\Models\ExamAdmission;
use App\Models\ExamAttempt;
use App\Models\ExamSession;
use App\Models\MarketingLead;
use App\Models\Payment;
use App\Models\ReportDefinition;
use App\Models\ReportRun;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use Throwable;

class RunReportAction
{
    use ReadsAnalyticsDataSafely;

    /**
     * @param  array<string, mixed>  $filters
     * @return array{run: ReportRun, summary: array<string, mixed>, filters: array<string, mixed>}
     *
     * @throws Throwable
     */
    public function handle(ReportDefinition|int|string $definition, array $filters = [], ?User $user = null): array
    {
        $definition = $this->resolveDefinition($definition);

        $this->authorizeAnalyticsAccess($user, 'analytics.reports.run');
        $this->authorizeAnalyticsAccess($user, $this->definitionPermissions($definition));

        if (! $definition->is_active) {
            throw new InvalidArgumentException(tkey('analytics.validation.inactive_report'));
        }

        $filters = app(ResolveAnalyticsFiltersAction::class)->handle(
            array_merge($definition->default_filters ?? [], $filters),
            $user,
        );

        $startedAt = now();
        $run = ReportRun::query()->create($this->analyticsExistingAttributes(ReportRun::class, [
            'report_definition_id' => $definition->id,
            'user_id' => $user?->id,
            'status' => AnalyticsRunStatus::Running,
            'period_start' => $filters['period_start'] ?? null,
            'period_end' => $filters['period_end'] ?? null,
            'started_at' => $startedAt,
            'row_count' => 0,
            'filters' => $filters,
            'summary' => [],
            'result_payload' => [],
            'metadata' => ['data_source' => $this->definitionDataSource($definition)],
            'created_by_id' => $user?->id,
        ]));

        try {
            $summary = $this->summaryFor($definition, $filters);

            $run->forceFill($this->analyticsExistingAttributes($run, [
                'status' => AnalyticsRunStatus::Completed,
                'finished_at' => now(),
                'row_count' => (int) ($summary['row_count'] ?? 0),
                'summary' => $summary,
                'result_payload' => ['summary' => $summary],
                'metadata' => [
                    'data_source' => $this->definitionDataSource($definition),
                    'optional_modules' => $summary['modules'] ?? [],
                ],
            ]))->save();

            return [
                'run' => $run->refresh(),
                'summary' => $summary,
                'filters' => $filters,
            ];
        } catch (Throwable $exception) {
            $run->forceFill($this->analyticsExistingAttributes($run, [
                'status' => AnalyticsRunStatus::Failed,
                'finished_at' => now(),
                'error_message' => $exception->getMessage(),
            ]))->save();

            throw $exception;
        }
    }

    private function resolveDefinition(ReportDefinition|int|string $definition): ReportDefinition
    {
        if ($definition instanceof ReportDefinition) {
            return $definition;
        }

        if (! $this->analyticsTableExists(ReportDefinition::class)) {
            throw new InvalidArgumentException(tkey('analytics.validation.inactive_report'));
        }

        return is_numeric($definition)
            ? ReportDefinition::query()->findOrFail((int) $definition)
            : ReportDefinition::query()->where('code', (string) $definition)->firstOrFail();
    }

    /**
     * @return array<int, string>
     */
    private function definitionPermissions(ReportDefinition $definition): array
    {
        $permissions = $definition->getAttribute('permissions');

        return is_array($permissions)
            ? array_values(array_filter($permissions, fn (mixed $permission): bool => is_string($permission) && $permission !== ''))
            : [];
    }

    private function definitionDataSource(ReportDefinition $definition): ?string
    {
        return $definition->getAttribute('data_source') ?: $definition->getAttribute('source_model');
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    private function summaryFor(ReportDefinition $definition, array $filters): array
    {
        return match ($definition->code) {
            'crm_lead_pipeline' => $this->leadPipelineSummary($filters),
            'student_enrollment_health' => $this->studentEnrollmentSummary($filters),
            'schedule_lesson_utilization' => $this->lessonUtilizationSummary($filters),
            'finance_payment_summary' => $this->paymentSummary($filters),
            'exam_readiness' => $this->examReadinessSummary($filters),
            default => $this->genericSummary($definition, $filters),
        };
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    private function leadPipelineSummary(array $filters): array
    {
        $rowCount = $this->analyticsCount(
            MarketingLead::class,
            fn (Builder $query): Builder => $this->applyAnalyticsFilters($query, $filters),
        );

        return [
            'row_count' => $rowCount,
            'by_status' => $this->analyticsCountByKnownValues(
                MarketingLead::class,
                'status',
                array_map(fn (LeadStatus $status): string => $status->value, LeadStatus::cases()),
                $filters,
            ),
            'converted' => $this->analyticsCount(
                MarketingLead::class,
                fn (Builder $query): Builder => $this->applyAnalyticsFilters($query->converted(), $filters),
            ),
            'conversion_ready' => $this->analyticsCount(
                MarketingLead::class,
                fn (Builder $query): Builder => $this->applyAnalyticsFilters($query, $filters)
                    ->where('status', LeadStatus::ReadyToEnroll->value),
            ),
            'overdue_follow_ups' => $this->analyticsCount(
                MarketingLead::class,
                fn (Builder $query): Builder => $this->applyAnalyticsDimensions($query->overdueFollowUp(), $filters),
            ),
            'modules' => $this->analyticsModuleAvailability(['crm_leads' => MarketingLead::class]),
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    private function studentEnrollmentSummary(array $filters): array
    {
        return [
            'row_count' => $this->analyticsCount(
                Enrollment::class,
                fn (Builder $query): Builder => $this->applyAnalyticsFilters($query, $filters),
            ),
            'by_status' => $this->analyticsCountByKnownValues(
                Enrollment::class,
                'status',
                array_map(fn (EnrollmentStatus $status): string => $status->value, EnrollmentStatus::cases()),
                $filters,
            ),
            'active' => $this->analyticsCount(
                Enrollment::class,
                fn (Builder $query): Builder => $this->applyAnalyticsFilters($query->active(), $filters),
            ),
            'completed' => $this->analyticsCount(
                Enrollment::class,
                fn (Builder $query): Builder => $this->applyAnalyticsFilters($query->completed(), $filters),
            ),
            'waiting_documents' => $this->analyticsCount(
                Enrollment::class,
                fn (Builder $query): Builder => $this->applyAnalyticsFilters($query->waitingDocuments(), $filters),
            ),
            'waiting_payment' => $this->analyticsCount(
                Enrollment::class,
                fn (Builder $query): Builder => $this->applyAnalyticsFilters($query->waitingPayment(), $filters),
            ),
            'modules' => $this->analyticsModuleAvailability(['enrollments' => Enrollment::class]),
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    private function lessonUtilizationSummary(array $filters): array
    {
        return [
            'row_count' => $this->analyticsCount(
                DrivingLesson::class,
                fn (Builder $query): Builder => $this->applyAnalyticsFilters($query, $filters),
            ),
            'by_status' => $this->analyticsCountByKnownValues(
                DrivingLesson::class,
                'status',
                array_map(fn (LessonStatus $status): string => $status->value, LessonStatus::cases()),
                $filters,
            ),
            'scheduled_today' => $this->analyticsCount(
                DrivingLesson::class,
                fn (Builder $query): Builder => $this->applyAnalyticsDimensions($query, $filters)
                    ->where('status', LessonStatus::Scheduled->value)
                    ->whereBetween('starts_at', [now()->startOfDay(), now()->endOfDay()]),
            ),
            'upcoming' => $this->analyticsCount(
                DrivingLesson::class,
                fn (Builder $query): Builder => $this->applyAnalyticsDimensions($query->upcoming(), $filters),
            ),
            'modules' => $this->analyticsModuleAvailability(['driving_lessons' => DrivingLesson::class]),
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    private function paymentSummary(array $filters): array
    {
        return [
            'row_count' => $this->analyticsCount(
                Payment::class,
                fn (Builder $query): Builder => $this->applyAnalyticsFilters($query, $filters),
            ),
            'by_status' => $this->analyticsCountByKnownValues(
                Payment::class,
                'status',
                array_map(fn (PaymentStatus $status): string => $status->value, PaymentStatus::cases()),
                $filters,
            ),
            'paid_count' => $this->analyticsCount(
                Payment::class,
                fn (Builder $query): Builder => $this->applyAnalyticsFilters($query->paid(), $filters, 'paid_at'),
            ),
            'paid_revenue_cents' => (int) $this->analyticsSum(
                Payment::class,
                'amount_cents',
                fn (Builder $query): Builder => $this->applyAnalyticsFilters($query->paid(), $filters, 'paid_at'),
            ),
            'modules' => $this->analyticsModuleAvailability(['finance' => Payment::class]),
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    private function examReadinessSummary(array $filters): array
    {
        return [
            'row_count' => $this->analyticsCount(
                ExamAdmission::class,
                fn (Builder $query): Builder => $this->applyAnalyticsFilters($query, $filters),
            ),
            'ready_admissions' => $this->analyticsCount(
                ExamAdmission::class,
                fn (Builder $query): Builder => $this->applyAnalyticsFilters($query, $filters)
                    ->where('status', ExamAdmissionStatus::Ready->value),
            ),
            'blocked_admissions' => $this->analyticsCount(
                ExamAdmission::class,
                fn (Builder $query): Builder => $this->applyAnalyticsFilters($query, $filters)
                    ->where('status', ExamAdmissionStatus::Blocked->value),
            ),
            'upcoming_sessions' => $this->analyticsCount(
                ExamSession::class,
                fn (Builder $query): Builder => $this->applyAnalyticsDimensions($query->upcoming(), $filters),
            ),
            'sessions_by_status' => $this->analyticsCountByKnownValues(
                ExamSession::class,
                'status',
                array_map(fn (ExamSessionStatus $status): string => $status->value, ExamSessionStatus::cases()),
                $filters,
            ),
            'passed_attempts' => $this->analyticsCount(
                ExamAttempt::class,
                fn (Builder $query): Builder => $this->applyAnalyticsFilters($query, $filters)
                    ->where('status', ExamAttemptStatus::Passed->value),
            ),
            'failed_attempts' => $this->analyticsCount(
                ExamAttempt::class,
                fn (Builder $query): Builder => $this->applyAnalyticsFilters($query, $filters)
                    ->where('status', ExamAttemptStatus::Failed->value),
            ),
            'modules' => $this->analyticsModuleAvailability([
                'exam_admissions' => ExamAdmission::class,
                'exam_sessions' => ExamSession::class,
                'exam_attempts' => ExamAttempt::class,
            ]),
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    private function genericSummary(ReportDefinition $definition, array $filters): array
    {
        $source = $this->definitionDataSource($definition);

        if (! is_string($source) || ! is_subclass_of($source, Model::class)) {
            return [
                'row_count' => 0,
                'records' => 0,
                'modules' => [],
            ];
        }

        return [
            'row_count' => $this->analyticsCount(
                $source,
                fn (Builder $query): Builder => $this->applyAnalyticsFilters($query, $filters),
            ),
            'records' => $this->analyticsCount(
                $source,
                fn (Builder $query): Builder => $this->applyAnalyticsFilters($query, $filters),
            ),
            'modules' => $this->analyticsModuleAvailability([class_basename($source) => $source]),
        ];
    }
}
