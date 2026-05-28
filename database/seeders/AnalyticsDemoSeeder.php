<?php

namespace Database\Seeders;

use App\Enums\AnalyticsDashboardAudience;
use App\Enums\AnalyticsReportType;
use App\Enums\DashboardWidgetType;
use App\Enums\KpiDirection;
use App\Enums\KpiPeriod;
use App\Models\AnalyticsCache;
use App\Models\AnalyticsDashboard;
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
        $dashboard = $this->seedDashboards();

        $this->seedWidgets($dashboard);
        $this->seedReports();
        $metrics = $this->seedMetrics();
        $this->seedTargetsAndSnapshots($metrics);
        $this->seedCache();
        $this->seedPreferences($dashboard);
    }

    private function seedDashboards(): AnalyticsDashboard
    {
        return AnalyticsDashboard::query()->updateOrCreate(
            ['code' => 'owner_overview'],
            AnalyticsDashboard::factory()
                ->default()
                ->audience(AnalyticsDashboardAudience::Owner)
                ->make([
                    'code' => 'owner_overview',
                    'name_translations' => $this->translations('Owner overview'),
                    'description_translations' => $this->translations('Local driving school owner dashboard.'),
                    'sort_order' => 10,
                ])
                ->only((new AnalyticsDashboard)->getFillable()),
        );
    }

    private function seedWidgets(AnalyticsDashboard $dashboard): void
    {
        foreach ($this->widgetDefinitions() as $index => $definition) {
            DashboardWidget::query()->updateOrCreate(
                ['code' => $definition['code']],
                DashboardWidget::factory()
                    ->forDashboard($dashboard)
                    ->system()
                    ->make([
                        ...$definition,
                        'analytics_dashboard_id' => $dashboard->id,
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

    private function seedPreferences(AnalyticsDashboard $dashboard): void
    {
        $user = User::query()->first();

        if ($user === null) {
            return;
        }

        UserDashboardPreference::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'dashboard' => $dashboard->code,
            ],
            UserDashboardPreference::factory()
                ->forDashboard($dashboard)
                ->make([
                    'user_id' => $user->id,
                    'analytics_dashboard_id' => $dashboard->id,
                    'dashboard' => $dashboard->code,
                    'layout' => [
                        'widgets' => collect($this->widgetDefinitions())
                            ->map(fn (array $definition, int $index): array => [
                                'code' => $definition['code'],
                                'width' => $definition['width'],
                                'height' => $definition['height'],
                                'sort_order' => ($index + 1) * 10,
                            ])
                            ->values()
                            ->all(),
                    ],
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
            $this->counterWidget('open_leads', 'Open leads', 'open_leads', 3, 1),
            $this->counterWidget('active_students', 'Active students', 'active_students', 3, 1),
            $this->counterWidget('active_enrollments', 'Active enrollments', 'active_enrollments', 3, 1),
            $this->counterWidget('lessons_today', 'Lessons today', 'lessons_today', 3, 1),
            $this->counterWidget('scheduled_exams', 'Scheduled exams', 'scheduled_exams', 3, 1),
            $this->counterWidget('paid_revenue', 'Paid revenue', 'paid_revenue_eur', 6, 1),
            $this->counterWidget('pending_documents', 'Pending documents', 'pending_documents', 3, 1),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function counterWidget(string $code, string $name, string $metricCode, int $width, int $height): array
    {
        $translations = $this->translations($name);

        return [
            'code' => $code,
            'title_translations' => $translations,
            'name_translations' => $translations,
            'description_translations' => $translations,
            'widget_type' => DashboardWidgetType::Counter->value,
            'metric_code' => $metricCode,
            'config' => ['metric' => $metricCode],
            'filters' => [],
            'width' => $width,
            'height' => $height,
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
