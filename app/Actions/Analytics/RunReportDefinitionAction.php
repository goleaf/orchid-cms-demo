<?php

namespace App\Actions\Analytics;

use App\Enums\AnalyticsRunStatus;
use App\Enums\EnrollmentStatus;
use App\Enums\ExamAdmissionStatus;
use App\Enums\ExamAttemptStatus;
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
use BackedEnum;
use Illuminate\Database\Eloquent\Builder;
use InvalidArgumentException;

class RunReportDefinitionAction
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function handle(ReportDefinition $definition, array $filters = [], ?User $user = null): ReportRun
    {
        if (! $definition->is_active) {
            throw new InvalidArgumentException(tkey('analytics.validation.inactive_report'));
        }

        $startedAt = now();
        $summary = $this->summaryFor($definition, $filters);

        return ReportRun::query()->create([
            'report_definition_id' => $definition->id,
            'status' => AnalyticsRunStatus::Completed,
            'period_start' => $filters['period_start'] ?? null,
            'period_end' => $filters['period_end'] ?? null,
            'started_at' => $startedAt,
            'finished_at' => now(),
            'row_count' => (int) ($summary['row_count'] ?? 0),
            'filters' => $filters,
            'summary' => $summary,
            'result_payload' => ['summary' => $summary],
            'created_by_id' => $user?->id,
        ]);
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
            default => ['row_count' => 0, 'records' => 0],
        };
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    private function leadPipelineSummary(array $filters): array
    {
        $scope = fn (Builder $query): Builder => $this->applyCommonFilters($query, $filters);

        return [
            'row_count' => $this->applyCommonFilters(MarketingLead::query(), $filters)->count(),
            'by_status' => MarketingLead::reportCountByStatus($scope),
            'by_source' => MarketingLead::reportCountBySource($scope),
            'converted' => $this->applyCommonFilters(MarketingLead::query()->converted(), $filters)->count(),
            'conversion_ready' => MarketingLead::reportConversionReadyCount($scope),
            'overdue_follow_ups' => MarketingLead::reportOverdueFollowUpCount($scope),
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    private function studentEnrollmentSummary(array $filters): array
    {
        $query = $this->applyCommonFilters(Enrollment::query()->select(['id', 'status', 'training_program_id']), $filters);

        return [
            'row_count' => $this->applyCommonFilters(Enrollment::query(), $filters)->count(),
            'by_status' => $this->countByAttribute($query, 'status'),
            'active' => $this->applyCommonFilters(Enrollment::query()->active(), $filters)->count(),
            'completed' => $this->applyCommonFilters(Enrollment::query()->completed(), $filters)->count(),
            'waiting_documents' => $this->applyCommonFilters(Enrollment::query()->where('status', EnrollmentStatus::WaitingDocuments->value), $filters)->count(),
            'waiting_payment' => $this->applyCommonFilters(Enrollment::query()->where('status', EnrollmentStatus::WaitingPayment->value), $filters)->count(),
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    private function lessonUtilizationSummary(array $filters): array
    {
        $query = $this->applyCommonFilters(DrivingLesson::query()->select(['id', 'status', 'training_program_id', 'branch_id']), $filters);

        return [
            'row_count' => $this->applyCommonFilters(DrivingLesson::query(), $filters)->count(),
            'by_status' => $this->countByAttribute($query, 'status'),
            'scheduled_today' => $this->applyCommonFilters(DrivingLesson::query(), $filters)
                ->where('status', LessonStatus::Scheduled->value)
                ->whereBetween('starts_at', [now()->startOfDay(), now()->endOfDay()])
                ->count(),
            'upcoming' => $this->applyCommonFilters(DrivingLesson::query()->upcoming(), $filters)->count(),
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    private function paymentSummary(array $filters): array
    {
        $query = $this->applyCommonFilters(Payment::query()->select(['id', 'status']), $filters);

        return [
            'row_count' => $this->applyCommonFilters(Payment::query(), $filters)->count(),
            'by_status' => $this->countByAttribute($query, 'status'),
            'paid_count' => $this->applyCommonFilters(Payment::query()->where('status', PaymentStatus::Paid->value), $filters)->count(),
            'paid_revenue_cents' => (int) $this->applyCommonFilters(Payment::query()->where('status', PaymentStatus::Paid->value), $filters)->sum('amount_cents'),
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    private function examReadinessSummary(array $filters): array
    {
        return [
            'row_count' => $this->applyCommonFilters(ExamAdmission::query(), $filters)->count(),
            'ready_admissions' => $this->applyCommonFilters(ExamAdmission::query()->where('status', ExamAdmissionStatus::Ready->value), $filters)->count(),
            'blocked_admissions' => $this->applyCommonFilters(ExamAdmission::query()->where('status', ExamAdmissionStatus::Blocked->value), $filters)->count(),
            'upcoming_sessions' => $this->applyCommonFilters(ExamSession::query()->upcoming(), $filters)->count(),
            'passed_attempts' => $this->applyCommonFilters(ExamAttempt::query()->where('status', ExamAttemptStatus::Passed->value), $filters)->count(),
            'failed_attempts' => $this->applyCommonFilters(ExamAttempt::query()->where('status', ExamAttemptStatus::Failed->value), $filters)->count(),
        ];
    }

    /**
     * @param  Builder<\Illuminate\Database\Eloquent\Model>  $query
     * @param  array<string, mixed>  $filters
     * @return Builder<\Illuminate\Database\Eloquent\Model>
     */
    private function applyCommonFilters(Builder $query, array $filters): Builder
    {
        if (filled($filters['period_start'] ?? null)) {
            $query->where('created_at', '>=', $filters['period_start']);
        }

        if (filled($filters['period_end'] ?? null)) {
            $query->where('created_at', '<=', $filters['period_end']);
        }

        if (filled($filters['branch_id'] ?? null) && $this->modelHasColumn($query, 'branch_id')) {
            $query->where('branch_id', (int) $filters['branch_id']);
        }

        if (filled($filters['training_program_id'] ?? null) && $this->modelHasColumn($query, 'training_program_id')) {
            $query->where('training_program_id', (int) $filters['training_program_id']);
        }

        return $query;
    }

    /**
     * @param  Builder<\Illuminate\Database\Eloquent\Model>  $query
     * @return array<string, int>
     */
    private function countByAttribute(Builder $query, string $attribute): array
    {
        $counts = [];

        foreach ($query->orderBy('id')->cursor() as $record) {
            $value = $record->getAttribute($attribute);

            if ($value instanceof BackedEnum) {
                $value = $value->value;
            }

            $key = filled($value) ? (string) $value : 'none';
            $counts[$key] = ($counts[$key] ?? 0) + 1;
        }

        return $counts;
    }

    /**
     * @param  Builder<\Illuminate\Database\Eloquent\Model>  $query
     */
    private function modelHasColumn(Builder $query, string $column): bool
    {
        return in_array($column, $query->getModel()->getFillable(), true);
    }
}
