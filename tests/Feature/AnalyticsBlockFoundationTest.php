<?php

namespace Tests\Feature;

use App\Actions\Analytics\CreateReportExportAction;
use App\Actions\Analytics\GetOwnerDashboardAction;
use App\Actions\Analytics\RecordKpiSnapshotAction;
use App\Actions\Analytics\RefreshAnalyticsCacheAction;
use App\Actions\Analytics\RunReportDefinitionAction;
use App\Actions\Analytics\SaveUserDashboardPreferencesAction;
use App\Enums\AnalyticsDashboardAudience;
use App\Enums\AnalyticsReportType;
use App\Enums\DashboardWidgetType;
use App\Enums\DocumentStatus;
use App\Enums\KpiDirection;
use App\Enums\KpiPeriod;
use App\Enums\KpiSnapshotStatus;
use App\Enums\LeadStatus;
use App\Enums\LessonStatus;
use App\Enums\PaymentStatus;
use App\Enums\ReportExportFormat;
use App\Http\Requests\Analytics\DashboardPreferenceRequest;
use App\Http\Requests\Analytics\KpiMetricRequest;
use App\Http\Requests\Analytics\KpiTargetRequest;
use App\Http\Requests\Analytics\ReportDefinitionRequest;
use App\Http\Requests\Analytics\ReportExportRequest;
use App\Http\Requests\Analytics\RunReportRequest;
use App\Models\AnalyticsCache;
use App\Models\AnalyticsDashboard;
use App\Models\DashboardWidget;
use App\Models\DrivingLesson;
use App\Models\KpiMetric;
use App\Models\KpiSnapshot;
use App\Models\KpiTarget;
use App\Models\MarketingLead;
use App\Models\NotificationDeliveryLog;
use App\Models\Payment;
use App\Models\ReportDefinition;
use App\Models\ReportExport;
use App\Models\ReportRun;
use App\Models\Student;
use App\Models\StudentDocument;
use App\Models\StudentEnrollment;
use App\Models\User;
use App\Models\UserDashboardPreference;
use App\Rules\ActiveAnalyticsDashboardRule;
use App\Rules\ActiveKpiMetricRule;
use App\Rules\ActiveReportDefinitionRule;
use App\Rules\AnalyticsCodeRule;
use App\Rules\AnalyticsDateRangeRule;
use App\Rules\DashboardWidgetCodeRule;
use App\Support\Access\SuperadminPermissions;
use Database\Seeders\AnalyticsDemoSeeder;
use Database\Seeders\AnalyticsTranslationSeeder;
use Database\Seeders\LanguageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class AnalyticsBlockFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_analytics_schema_models_factories_and_local_product_boundaries_exist(): void
    {
        $tables = [
            'analytics_dashboards',
            'dashboard_widgets',
            'report_definitions',
            'report_runs',
            'report_exports',
            'kpi_metrics',
            'kpi_targets',
            'kpi_snapshots',
            'analytics_cache',
            'user_dashboard_preferences',
        ];

        foreach ($tables as $table) {
            $this->assertTrue(Schema::hasTable($table), $table);
            $this->assertEmpty(array_intersect(
                ['tenant_id', 'company_id', 'subscription_id', 'reseller_id', 'platform_owner_id'],
                Schema::getColumnListing($table),
            ), $table);
        }

        $user = User::factory()->create();
        $dashboard = AnalyticsDashboard::factory()
            ->createdBy($user)
            ->default()
            ->audience(AnalyticsDashboardAudience::Owner)
            ->create(['code' => 'owner_overview']);
        $widget = DashboardWidget::factory()
            ->forDashboard($dashboard)
            ->createdBy($user)
            ->type(DashboardWidgetType::Counter)
            ->create(['code' => 'owner_open_leads']);
        $definition = ReportDefinition::factory()->createdBy($user)->type(AnalyticsReportType::Sales)->create();
        $run = ReportRun::factory()->forDefinition($definition)->createdBy($user)->create();
        $export = ReportExport::factory()->forRun($run)->createdBy($user)->format(ReportExportFormat::Json)->create();
        $metric = KpiMetric::factory()->createdBy($user)->create(['code' => 'open_leads']);
        $target = KpiTarget::factory()->forMetric($metric)->assignedTo($user)->create();
        $snapshot = KpiSnapshot::factory()->forMetric($metric)->create();
        $cache = AnalyticsCache::factory()->createdBy($user)->create();
        $preference = UserDashboardPreference::factory()
            ->forDashboard($dashboard)
            ->create(['user_id' => $user->id]);

        $this->assertTrue($dashboard->creator->is($user));
        $this->assertTrue($dashboard->widgets->first()->is($widget));
        $this->assertTrue($widget->creator->is($user));
        $this->assertTrue($widget->dashboard->is($dashboard));
        $this->assertTrue($definition->runs->first()->is($run));
        $this->assertTrue($run->exports->first()->is($export));
        $this->assertTrue($metric->targets->first()->is($target));
        $this->assertTrue($metric->snapshots->first()->is($snapshot));
        $this->assertTrue($cache->isFresh());
        $this->assertTrue($user->dashboardPreferences->first()->is($preference));
        $this->assertTrue($preference->analyticsDashboard->is($dashboard));
    }

    public function test_owner_dashboard_report_export_kpi_cache_and_preference_actions_work(): void
    {
        $this->seed([LanguageSeeder::class, AnalyticsTranslationSeeder::class, AnalyticsDemoSeeder::class]);

        MarketingLead::factory()->create(['status' => LeadStatus::New]);
        MarketingLead::factory()->create([
            'status' => LeadStatus::BecameStudent,
            'converted_at' => now(),
        ]);
        Student::factory()->active()->create();
        StudentEnrollment::factory()->active()->create();
        DrivingLesson::factory()->create([
            'status' => LessonStatus::Scheduled,
            'starts_at' => now()->setTime(10, 0),
            'ends_at' => now()->setTime(11, 0),
        ]);
        Payment::factory()->create([
            'status' => PaymentStatus::Paid,
            'amount_cents' => 12500,
        ]);
        StudentDocument::factory()->create(['status' => DocumentStatus::Submitted]);
        NotificationDeliveryLog::factory()->create(['status' => NotificationDeliveryLog::STATUS_QUEUED]);

        $dashboard = app(GetOwnerDashboardAction::class)->handle();
        $this->assertGreaterThanOrEqual(1, $dashboard['metrics']['open_leads']);
        $this->assertGreaterThanOrEqual(1, $dashboard['metrics']['converted_leads']);
        $this->assertGreaterThanOrEqual(1, $dashboard['metrics']['active_students']);
        $this->assertGreaterThanOrEqual(1, $dashboard['metrics']['active_enrollments']);
        $this->assertGreaterThanOrEqual(1, $dashboard['metrics']['lessons_today']);
        $this->assertGreaterThanOrEqual(12500, $dashboard['metrics']['paid_revenue_cents']);
        $this->assertGreaterThanOrEqual(1, $dashboard['widgets']->count());
        $this->assertGreaterThanOrEqual(1, $dashboard['reportDefinitions']->count());

        $user = $this->userWithPermissions([
            'analytics.reports.run',
            'analytics.reports.export',
            'analytics.kpis.manage',
            'analytics.preferences.manage',
        ]);
        $definition = ReportDefinition::query()->where('code', 'crm_lead_pipeline')->firstOrFail();

        $run = app(RunReportDefinitionAction::class)->handle($definition, [
            'period_start' => now()->subMonth()->toDateString(),
            'period_end' => now()->toDateString(),
        ], $user);
        $this->assertSame($definition->id, $run->report_definition_id);
        $this->assertGreaterThanOrEqual(2, $run->row_count);
        $this->assertArrayHasKey('by_status', $run->summary);

        $export = app(CreateReportExportAction::class)->handle($definition, ReportExportFormat::Csv, $run, $user);
        $this->assertSame($run->id, $export->report_run_id);
        $this->assertSame(ReportExportFormat::Csv, $export->format);

        $metric = KpiMetric::query()->where('code', 'open_leads')->firstOrFail();
        KpiTarget::query()->create([
            'kpi_metric_id' => $metric->id,
            'period' => KpiPeriod::Day,
            'starts_on' => now()->subDay()->toDateString(),
            'target_value' => 1,
            'warning_value' => 1,
            'direction' => KpiDirection::Increase,
            'created_by_id' => $user->id,
        ]);
        $snapshot = app(RecordKpiSnapshotAction::class)->handle($metric, KpiPeriod::Day, now(), null, ['source' => 'test']);
        $this->assertSame(KpiSnapshotStatus::OnTrack, $snapshot->status);

        $cache = app(RefreshAnalyticsCacheAction::class)->handle('owner_dashboard.test', ['open_leads' => 1], user: $user);
        $this->assertSame(['open_leads' => 1], $cache->value);
        $this->assertTrue($cache->isFresh());

        $preference = app(SaveUserDashboardPreferencesAction::class)->handle($user, [
            'visible_widget_codes' => ['open_leads'],
            'widget_order' => ['open_leads'],
            'refresh_interval_seconds' => 120,
        ]);
        $this->assertSame(['open_leads'], $preference->visible_widget_codes);
        $this->assertSame('owner_overview', $preference->dashboard);
        $this->assertSame(['widgets' => [['code' => 'open_leads', 'sort_order' => 10]]], $preference->layout);
        $this->assertNotNull($preference->analytics_dashboard_id);
        $this->assertSame(120, $preference->refresh_interval_seconds);
    }

    public function test_analytics_rules_form_requests_translations_and_permissions_are_wired(): void
    {
        $this->seed([LanguageSeeder::class, AnalyticsTranslationSeeder::class, AnalyticsDemoSeeder::class]);

        $invalidCode = Validator::make(['code' => 'Bad Code'], [
            'code' => [new AnalyticsCodeRule],
        ]);
        $this->assertTrue($invalidCode->fails());
        $this->assertSame(tkey('analytics.validation.invalid_code'), $invalidCode->errors()->first('code'));

        $dateRange = Validator::make([
            'filters' => [
                'period_start' => now()->addDay()->toDateString(),
                'period_end' => now()->toDateString(),
            ],
        ], [
            'filters.period_start' => [new AnalyticsDateRangeRule],
        ]);
        $this->assertTrue($dateRange->fails());

        $inactiveReport = ReportDefinition::factory()->inactive()->create();
        $reportValidator = Validator::make(['report' => $inactiveReport->id], [
            'report' => [new ActiveReportDefinitionRule],
        ]);
        $this->assertTrue($reportValidator->fails());

        $inactiveMetric = KpiMetric::factory()->inactive()->create();
        $metricValidator = Validator::make(['metric' => $inactiveMetric->id], [
            'metric' => [new ActiveKpiMetricRule],
        ]);
        $this->assertTrue($metricValidator->fails());

        $inactiveDashboard = AnalyticsDashboard::factory()->inactive()->create();
        $dashboardValidator = Validator::make(['dashboard' => $inactiveDashboard->id], [
            'dashboard' => [new ActiveAnalyticsDashboardRule],
        ]);
        $this->assertTrue($dashboardValidator->fails());

        $widgetValidator = Validator::make(['widget' => 'missing_widget'], [
            'widget' => [new DashboardWidgetCodeRule],
        ]);
        $this->assertTrue($widgetValidator->fails());

        foreach ([
            ReportDefinitionRequest::class => 'analytics.reports.manage',
            RunReportRequest::class => 'analytics.reports.run',
            ReportExportRequest::class => 'analytics.reports.export',
            KpiMetricRequest::class => 'analytics.kpis.manage',
            KpiTargetRequest::class => 'analytics.kpi_targets.manage',
            DashboardPreferenceRequest::class => 'analytics.preferences.manage',
        ] as $requestClass => $permission) {
            $request = $requestClass::create('/', 'POST');
            $request->setUserResolver(fn (): User => $this->userWithPermissions([$permission]));
            $this->assertTrue($request->authorize(), $requestClass);
        }

        foreach ([
            'analytics.dashboard.view',
            'analytics.reports.manage',
            'analytics.reports.run',
            'analytics.reports.export',
            'analytics.kpis.manage',
            'analytics.kpi_targets.manage',
            'analytics.preferences.manage',
            'analytics.cache.view',
        ] as $permission) {
            $this->assertContains($permission, SuperadminPermissions::all());
            $this->assertNotSame('permissions.'.$permission, tkey('permissions.'.$permission));
        }
    }

    public function test_analytics_seeders_and_orchid_dashboard_route_are_available(): void
    {
        $this->seed([LanguageSeeder::class, AnalyticsTranslationSeeder::class, AnalyticsDemoSeeder::class]);

        $dashboard = AnalyticsDashboard::query()->where('code', 'owner_overview')->firstOrFail();
        $this->assertTrue($dashboard->is_default);
        $this->assertSame(AnalyticsDashboardAudience::Owner, $dashboard->audience);
        $this->assertTrue($dashboard->widgets()->where('code', 'open_leads')->exists());
        $this->assertTrue(DashboardWidget::query()->where('code', 'open_leads')->exists());
        $this->assertTrue(ReportDefinition::query()->where('code', 'crm_lead_pipeline')->exists());
        $this->assertTrue(KpiMetric::query()->where('code', 'paid_revenue_eur')->exists());
        $this->assertTrue(Route::has('platform.analytics.dashboard'));
        $this->assertNotSame('analytics.dashboard.title', tkey('analytics.dashboard.title'));

        $this->actingAs($this->userWithPermissions(['analytics.dashboard.view']))
            ->get(route('platform.analytics.dashboard'))
            ->assertOk()
            ->assertSee(tkey('analytics.dashboard.title'));
    }

    /**
     * @param  array<int, string>  $permissions
     */
    private function userWithPermissions(array $permissions = []): User
    {
        $user = User::factory()->create();

        $user->forceFill([
            'permissions' => collect(['platform.index', 'platform.main'])
                ->merge($permissions)
                ->mapWithKeys(fn (string $permission): array => [$permission => true])
                ->all(),
        ])->save();

        return $user;
    }
}
