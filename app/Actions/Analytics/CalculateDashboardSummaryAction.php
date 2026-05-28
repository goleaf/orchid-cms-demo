<?php

namespace App\Actions\Analytics;

use App\Actions\Analytics\Concerns\ReadsAnalyticsDataSafely;
use App\Enums\DocumentStatus;
use App\Enums\LessonStatus;
use App\Models\DrivingLesson;
use App\Models\Enrollment;
use App\Models\ExamSession;
use App\Models\MarketingLead;
use App\Models\NotificationDeliveryLog;
use App\Models\Payment;
use App\Models\StudentDocument;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class CalculateDashboardSummaryAction
{
    use ReadsAnalyticsDataSafely;

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function handle(array $filters = [], ?User $user = null): array
    {
        $this->authorizeAnalyticsAccess($user, 'analytics.dashboard.view');

        $filters = app(ResolveAnalyticsFiltersAction::class)->handle($filters, $user);
        $modules = $this->analyticsModuleAvailability($this->moduleMap());

        $metrics = [
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
                fn (Builder $query): Builder => $this->applyAnalyticsFilters($query->active(), $filters),
            ),
            'lessons_today' => $this->analyticsCount(
                DrivingLesson::class,
                fn (Builder $query): Builder => $this->applyAnalyticsDimensions($query, $filters)
                    ->where('status', LessonStatus::Scheduled->value)
                    ->whereBetween('starts_at', [now()->startOfDay(), now()->endOfDay()]),
            ),
            'upcoming_lessons' => $this->analyticsCount(
                DrivingLesson::class,
                fn (Builder $query): Builder => $this->applyAnalyticsDimensions($query->upcoming(), $filters),
            ),
            'scheduled_exams' => $this->analyticsCount(
                ExamSession::class,
                fn (Builder $query): Builder => $this->applyAnalyticsDimensions($query->upcoming(), $filters),
            ),
            'paid_revenue_cents' => (int) $this->analyticsSum(
                Payment::class,
                'amount_cents',
                fn (Builder $query): Builder => $this->applyAnalyticsFilters($query->paid(), $filters, 'paid_at'),
            ),
            'pending_documents' => $this->analyticsCount(
                StudentDocument::class,
                fn (Builder $query): Builder => $this->applyAnalyticsFilters($query, $filters)
                    ->whereIn('status', [DocumentStatus::Missing->value, DocumentStatus::Submitted->value]),
            ),
            'queued_notifications' => $this->analyticsCount(
                NotificationDeliveryLog::class,
                fn (Builder $query): Builder => $this->applyAnalyticsFilters($query, $filters)
                    ->where('status', NotificationDeliveryLog::STATUS_QUEUED),
            ),
        ];

        $metrics['paid_revenue_eur'] = round($metrics['paid_revenue_cents'] / 100, 2);

        return [
            'metrics' => $metrics,
            'filters' => $filters,
            'modules' => $modules,
            'missing_modules' => array_keys(array_filter($modules, fn (bool $exists): bool => ! $exists)),
            'calculated_at' => now()->toISOString(),
        ];
    }

    /**
     * @return array<string, class-string<Model>>
     */
    private function moduleMap(): array
    {
        return [
            'crm_leads' => MarketingLead::class,
            'students' => StudentProfile::class,
            'enrollments' => Enrollment::class,
            'driving_lessons' => DrivingLesson::class,
            'exams' => ExamSession::class,
            'finance' => Payment::class,
            'documents' => StudentDocument::class,
            'notifications' => NotificationDeliveryLog::class,
        ];
    }
}
