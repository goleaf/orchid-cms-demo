<?php

namespace App\Actions\Analytics;

use App\Enums\AnalyticsDashboardAudience;
use App\Enums\DocumentStatus;
use App\Enums\EnrollmentStatus;
use App\Enums\LessonStatus;
use App\Enums\PaymentStatus;
use App\Models\AnalyticsDashboard;
use App\Models\DashboardWidget;
use App\Models\DrivingLesson;
use App\Models\Enrollment;
use App\Models\ExamSession;
use App\Models\KpiSnapshot;
use App\Models\MarketingLead;
use App\Models\NotificationDeliveryLog;
use App\Models\Payment;
use App\Models\ReportDefinition;
use App\Models\ReportRun;
use App\Models\StudentDocument;
use App\Models\StudentProfile;

class GetOwnerDashboardAction
{
    /**
     * @return array<string, mixed>
     */
    public function handle(): array
    {
        $dashboard = AnalyticsDashboard::query()
            ->active()
            ->forAudience(AnalyticsDashboardAudience::Owner)
            ->default()
            ->ordered()
            ->with(['widgets' => fn ($query) => $query->active()->ordered()])
            ->first();

        return [
            'dashboard' => $dashboard,
            'metrics' => $this->metrics(),
            'widgets' => $dashboard?->widgets ?? DashboardWidget::query()
                ->active()
                ->ordered()
                ->limit(12)
                ->get(),
            'reportDefinitions' => ReportDefinition::query()
                ->active()
                ->ordered()
                ->withCount(['runs', 'exports'])
                ->limit(10)
                ->get(),
            'recentReportRuns' => ReportRun::query()
                ->with(['definition:id,code,name_translations'])
                ->latestRuns()
                ->limit(10)
                ->get(),
            'kpiSnapshots' => KpiSnapshot::query()
                ->with(['metric:id,code,name_translations,unit'])
                ->latestSnapshots()
                ->limit(10)
                ->get(),
        ];
    }

    /**
     * @return array<string, int|float>
     */
    public function metrics(): array
    {
        return [
            'open_leads' => MarketingLead::query()->open()->count(),
            'converted_leads' => MarketingLead::query()->converted()->count(),
            'active_students' => StudentProfile::query()->active()->count(),
            'active_enrollments' => Enrollment::query()->where('status', EnrollmentStatus::Active->value)->count(),
            'lessons_today' => DrivingLesson::query()
                ->where('status', LessonStatus::Scheduled->value)
                ->whereBetween('starts_at', [now()->startOfDay(), now()->endOfDay()])
                ->count(),
            'scheduled_exams' => ExamSession::query()->upcoming()->count(),
            'paid_revenue_cents' => (int) Payment::query()->where('status', PaymentStatus::Paid->value)->sum('amount_cents'),
            'pending_documents' => StudentDocument::query()
                ->whereIn('status', [DocumentStatus::Missing->value, DocumentStatus::Submitted->value])
                ->count(),
            'queued_notifications' => NotificationDeliveryLog::query()
                ->where('status', NotificationDeliveryLog::STATUS_QUEUED)
                ->count(),
        ];
    }
}
