<?php

namespace Database\Seeders;

use App\Enums\AnalyticsReportType;
use App\Enums\KpiDirection;
use App\Enums\KpiPeriod;
use App\Models\AnalyticsCache;
use App\Models\DashboardWidget;
use App\Models\KpiMetric;
use App\Models\KpiSnapshot;
use App\Models\KpiTarget;
use App\Models\ReportDefinition;
use App\Models\User;
use App\Models\UserDashboardPreference;
use Illuminate\Database\Seeder;

class AnalyticsDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedWidgets();
        $this->seedReports();
        $metrics = $this->seedMetrics();
        $this->seedTargetsAndSnapshots($metrics);
        $this->seedCache();
        $this->seedPreferences();
    }

    private function seedWidgets(): void
    {
        foreach ($this->widgetDefinitions() as $index => $definition) {
            DashboardWidget::query()->updateOrCreate(
                ['code' => $definition['code']],
                DashboardWidget::factory()
                    ->system()
                    ->make([
                        ...$definition,
                        'sort_order' => ($index + 1) * 10,
                    ])
                    ->only((new DashboardWidget)->getFillable()),
            );
        }
    }

    private function seedReports(): void
    {
        foreach ($this->reportDefinitions() as $index => $definition) {
            ReportDefinition::query()->updateOrCreate(
                ['code' => $definition['code']],
                ReportDefinition::factory()
                    ->system()
                    ->make([
                        ...$definition,
                        'sort_order' => ($index + 1) * 10,
                    ])
                    ->only((new ReportDefinition)->getFillable()),
            );
        }
    }

    /**
     * @return array<string, KpiMetric>
     */
    private function seedMetrics(): array
    {
        $metrics = [];

        foreach ($this->metricDefinitions() as $index => $definition) {
            $metrics[$definition['code']] = KpiMetric::query()->updateOrCreate(
                ['code' => $definition['code']],
                KpiMetric::factory()
                    ->system()
                    ->make([
                        ...$definition,
                        'sort_order' => ($index + 1) * 10,
                    ])
                    ->only((new KpiMetric)->getFillable()),
            );
        }

        return $metrics;
    }

    /**
     * @param  array<string, KpiMetric>  $metrics
     */
    private function seedTargetsAndSnapshots(array $metrics): void
    {
        foreach ($metrics as $metric) {
            $target = KpiTarget::query()->firstOrCreate(
                [
                    'kpi_metric_id' => $metric->id,
                    'period' => KpiPeriod::Month->value,
                    'starts_on' => now()->startOfYear()->toDateString(),
                ],
                KpiTarget::factory()
                    ->forMetric($metric)
                    ->make([
                        'target_value' => $metric->code === 'paid_revenue_eur' ? 10000 : 25,
                        'warning_value' => $metric->code === 'paid_revenue_eur' ? 7500 : 15,
                        'direction' => KpiDirection::Increase,
                    ])
                    ->only((new KpiTarget)->getFillable()),
            );

            KpiSnapshot::query()->firstOrCreate(
                [
                    'kpi_metric_id' => $metric->id,
                    'period' => KpiPeriod::Day->value,
                    'snapshot_date' => now()->toDateString(),
                ],
                KpiSnapshot::factory()
                    ->forMetric($metric)
                    ->make([
                        'value' => 0,
                        'target_value' => $target->target_value,
                        'source_payload' => ['seeded' => true],
                    ])
                    ->only((new KpiSnapshot)->getFillable()),
            );
        }
    }

    private function seedCache(): void
    {
        AnalyticsCache::query()->updateOrCreate(
            ['key' => 'owner_dashboard.metrics'],
            AnalyticsCache::factory()
                ->make([
                    'key' => 'owner_dashboard.metrics',
                    'group' => 'dashboard',
                    'value' => ['seeded' => true],
                    'tags' => ['analytics', 'dashboard'],
                ])
                ->only((new AnalyticsCache)->getFillable()),
        );
    }

    private function seedPreferences(): void
    {
        $user = User::query()->first();

        if ($user === null) {
            return;
        }

        UserDashboardPreference::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'dashboard' => 'owner',
            ],
            UserDashboardPreference::factory()
                ->make([
                    'user_id' => $user->id,
                    'dashboard' => 'owner',
                    'visible_widget_codes' => array_column($this->widgetDefinitions(), 'code'),
                    'widget_order' => array_column($this->widgetDefinitions(), 'code'),
                ])
                ->only((new UserDashboardPreference)->getFillable()),
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function widgetDefinitions(): array
    {
        return [
            ['code' => 'open_leads', 'name_translations' => $this->translations('Open leads'), 'metric_code' => 'open_leads'],
            ['code' => 'active_students', 'name_translations' => $this->translations('Active students'), 'metric_code' => 'active_students'],
            ['code' => 'active_enrollments', 'name_translations' => $this->translations('Active enrollments'), 'metric_code' => 'active_enrollments'],
            ['code' => 'lessons_today', 'name_translations' => $this->translations('Lessons today'), 'metric_code' => 'lessons_today'],
            ['code' => 'scheduled_exams', 'name_translations' => $this->translations('Scheduled exams'), 'metric_code' => 'scheduled_exams'],
            ['code' => 'paid_revenue', 'name_translations' => $this->translations('Paid revenue'), 'metric_code' => 'paid_revenue_eur'],
            ['code' => 'pending_documents', 'name_translations' => $this->translations('Pending documents'), 'metric_code' => 'pending_documents'],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function reportDefinitions(): array
    {
        return [
            ['code' => 'crm_lead_pipeline', 'name_translations' => $this->translations('CRM lead pipeline'), 'report_type' => AnalyticsReportType::Sales, 'source_model' => 'App\\Models\\MarketingLead'],
            ['code' => 'student_enrollment_health', 'name_translations' => $this->translations('Student enrollment health'), 'report_type' => AnalyticsReportType::Education, 'source_model' => 'App\\Models\\Enrollment'],
            ['code' => 'schedule_lesson_utilization', 'name_translations' => $this->translations('Lesson utilization'), 'report_type' => AnalyticsReportType::Operational, 'source_model' => 'App\\Models\\DrivingLesson'],
            ['code' => 'finance_payment_summary', 'name_translations' => $this->translations('Payment summary'), 'report_type' => AnalyticsReportType::Finance, 'source_model' => 'App\\Models\\Payment'],
            ['code' => 'exam_readiness', 'name_translations' => $this->translations('Exam readiness'), 'report_type' => AnalyticsReportType::Exams, 'source_model' => 'App\\Models\\ExamAdmission'],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function metricDefinitions(): array
    {
        return [
            ['code' => 'open_leads', 'name_translations' => $this->translations('Open leads'), 'category' => 'crm', 'value_type' => 'number'],
            ['code' => 'converted_leads', 'name_translations' => $this->translations('Converted leads'), 'category' => 'crm', 'value_type' => 'number'],
            ['code' => 'active_students', 'name_translations' => $this->translations('Active students'), 'category' => 'education', 'value_type' => 'number'],
            ['code' => 'active_enrollments', 'name_translations' => $this->translations('Active enrollments'), 'category' => 'education', 'value_type' => 'number'],
            ['code' => 'lessons_today', 'name_translations' => $this->translations('Lessons today'), 'category' => 'operations', 'value_type' => 'number'],
            ['code' => 'scheduled_exams', 'name_translations' => $this->translations('Scheduled exams'), 'category' => 'exams', 'value_type' => 'number'],
            ['code' => 'paid_revenue_eur', 'name_translations' => $this->translations('Paid revenue'), 'category' => 'finance', 'value_type' => 'money', 'unit' => 'EUR'],
            ['code' => 'pending_documents', 'name_translations' => $this->translations('Pending documents'), 'category' => 'operations', 'value_type' => 'number'],
        ];
    }

    /**
     * @return array<string, string>
     */
    private function translations(string $value): array
    {
        return [
            'ru' => $value,
            'en' => $value,
            'lt' => $value,
            'pl' => $value,
        ];
    }
}
