<?php

namespace Tests\Feature;

use App\Enums\AnalyticsRunStatus;
use App\Enums\DashboardWidgetType;
use App\Enums\KpiPeriod;
use App\Enums\ReportExportFormat;
use App\Http\Requests\Analytics\ExportReportRequest;
use App\Http\Requests\Analytics\RefreshAnalyticsCacheRequest;
use App\Http\Requests\Analytics\RunReportRequest;
use App\Http\Requests\Analytics\StoreDashboardWidgetRequest;
use App\Http\Requests\Analytics\StoreKpiTargetRequest;
use App\Models\AnalyticsDashboard;
use App\Models\Branch;
use App\Models\KpiMetric;
use App\Models\KpiTarget;
use App\Models\ReportDefinition;
use App\Models\ReportRun;
use App\Models\User;
use Database\Seeders\AnalyticsTranslationSeeder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class AnalyticsFormRequestsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app()->setLocale('en');
        Cache::flush();
        $this->seed(AnalyticsTranslationSeeder::class);
        Cache::flush();
    }

    public function test_run_report_request_authorizes_filters_and_uses_translated_attributes(): void
    {
        $definition = ReportDefinition::factory()->create(['is_active' => true]);
        $branch = Branch::factory()->create();
        $manager = User::factory()->create();
        $user = $this->analyticsUser(['analytics.reports.run']);

        $request = $this->request(RunReportRequest::class, [
            'report_definition_id' => $definition->id,
            'filters' => [
                'period_start' => '2026-01-01',
                'period_end' => '2026-01-31',
                'branch_id' => $branch->id,
                'user_id' => $manager->id,
                'status' => 'open',
                'source' => 'website',
            ],
            'columns' => ['status', 'created_at'],
        ], $user);

        $validator = $this->validator($request);

        $this->assertTrue($request->authorize());
        $this->assertFalse($validator->fails(), json_encode($validator->errors()->toArray(), JSON_THROW_ON_ERROR));
        $this->assertSame(tkey('validation.attributes.analytics.report'), $request->attributes()['report_definition_id']);
        $this->assertNotSame('validation.attributes.analytics.report', $request->attributes()['report_definition_id']);

        $invalid = $this->validator($this->request(RunReportRequest::class, [
            'report_definition_id' => $definition->id,
            'filters' => ['tenant_id' => 1],
        ], $user));

        $this->assertTranslatedFailure($invalid, 'filters', 'analytics.validation.invalid_filter');
    }

    public function test_export_report_request_validates_format_and_export_permission_rules(): void
    {
        $definition = ReportDefinition::factory()->create(['is_active' => true]);
        $user = $this->analyticsUser(['analytics.reports.export']);
        $run = ReportRun::factory()
            ->forDefinition($definition)
            ->createdBy($user)
            ->create(['status' => AnalyticsRunStatus::Completed]);

        $request = $this->request(ExportReportRequest::class, [
            'report_definition_id' => $definition->id,
            'report_run_id' => $run->id,
            'format' => ReportExportFormat::Json->value,
        ], $user);

        $validator = $this->validator($request);

        $this->assertTrue($request->authorize());
        $this->assertFalse($validator->fails(), json_encode($validator->errors()->toArray(), JSON_THROW_ON_ERROR));

        $invalid = $this->validator($this->request(ExportReportRequest::class, [
            'report_definition_id' => $definition->id,
            'report_run_id' => $run->id,
            'format' => 'csv',
        ], $user));

        $this->assertTranslatedFailure($invalid, 'format', 'analytics.validation.invalid_format');
    }

    public function test_store_dashboard_widget_request_validates_config_filters_and_permissions(): void
    {
        $dashboard = AnalyticsDashboard::factory()->create(['is_active' => true]);
        $user = $this->analyticsUser(['analytics.preferences.manage']);

        $request = $this->request(StoreDashboardWidgetRequest::class, [
            'widget' => [
                'analytics_dashboard_id' => $dashboard->id,
                'code' => 'open_leads_counter',
                'widget_type' => DashboardWidgetType::Counter->value,
                'title_translations' => ['en' => 'Open leads'],
                'config' => ['metric' => 'open_leads', 'columns' => ['status']],
                'filters' => [
                    'period_type' => KpiPeriod::Month->value,
                    'period_start' => '2026-01-01',
                    'period_end' => '2026-01-31',
                ],
                'width' => 3,
                'height' => 1,
            ],
        ], $user);

        $validator = $this->validator($request);

        $this->assertTrue($request->authorize());
        $this->assertFalse($validator->fails(), json_encode($validator->errors()->toArray(), JSON_THROW_ON_ERROR));
        $this->assertSame(tkey('validation.attributes.analytics.widget_config'), $request->attributes()['widget.config']);

        $invalid = $this->validator($this->request(StoreDashboardWidgetRequest::class, [
            'widget' => [
                'analytics_dashboard_id' => $dashboard->id,
                'code' => 'bad_widget_config',
                'widget_type' => DashboardWidgetType::Counter->value,
                'title_translations' => ['en' => 'Bad widget'],
                'config' => ['tenant_id' => 1],
            ],
        ], $user));

        $this->assertTranslatedFailure($invalid, 'widget.config', 'analytics.validation.invalid_widget_config');
    }

    public function test_store_kpi_target_request_validates_period_value_and_uniqueness(): void
    {
        $metric = KpiMetric::factory()->create(['is_active' => true]);
        $branch = Branch::factory()->create();
        $staff = User::factory()->create();
        $user = $this->analyticsUser(['analytics.kpi_targets.manage']);

        $payload = [
            'target' => [
                'kpi_metric_id' => $metric->id,
                'period_type' => KpiPeriod::Month->value,
                'period_start' => '2026-02-01',
                'period_end' => '2026-02-28',
                'branch_id' => $branch->id,
                'user_id' => $staff->id,
                'target_value' => '25.00',
                'warning_threshold' => '15.00',
                'success_threshold' => '30.00',
            ],
        ];

        $request = $this->request(StoreKpiTargetRequest::class, $payload, $user);
        $validator = $this->validator($request);

        $this->assertTrue($request->authorize());
        $this->assertFalse($validator->fails(), json_encode($validator->errors()->toArray(), JSON_THROW_ON_ERROR));

        KpiTarget::factory()->forMetric($metric)->create([
            'period_type' => KpiPeriod::Month->value,
            'period_start' => '2026-02-01',
            'period_end' => '2026-02-28',
            'branch_id' => $branch->id,
            'user_id' => $staff->id,
        ]);

        $duplicate = $this->validator($this->request(StoreKpiTargetRequest::class, $payload, $user));

        $this->assertTranslatedFailure($duplicate, 'target.kpi_metric_id', 'analytics.validation.duplicate_kpi_target');
    }

    public function test_refresh_analytics_cache_request_validates_cache_key_period_and_filters(): void
    {
        $branch = Branch::factory()->create();
        $staff = User::factory()->create();
        $user = $this->analyticsUser(['analytics.cache.view']);

        $request = $this->request(RefreshAnalyticsCacheRequest::class, [
            'cache_key' => 'owner_dashboard.summary',
            'data' => ['open_leads' => 4],
            'tags' => ['analytics.dashboard'],
            'ttl_minutes' => 30,
            'module' => 'students',
            'period_type' => KpiPeriod::Month->value,
            'period_start' => '2026-01-01',
            'period_end' => '2026-01-31',
            'branch_id' => $branch->id,
            'user_id' => $staff->id,
            'filters' => ['status' => 'active'],
        ], $user);

        $validator = $this->validator($request);

        $this->assertTrue($request->authorize());
        $this->assertFalse($validator->fails(), json_encode($validator->errors()->toArray(), JSON_THROW_ON_ERROR));
        $this->assertSame(tkey('validation.attributes.analytics.cache_key'), $request->attributes()['cache_key']);

        $invalid = $this->validator($this->request(RefreshAnalyticsCacheRequest::class, [
            'cache_key' => 'Bad Key!',
            'data' => [],
        ], $user));

        $this->assertTranslatedFailure($invalid, 'cache_key', 'analytics.validation.invalid_cache_key');
    }

    /**
     * @param  class-string<FormRequest>  $requestClass
     * @param  array<string, mixed>  $payload
     */
    private function request(string $requestClass, array $payload, User $user): FormRequest
    {
        $request = $requestClass::create('/analytics-request-test', 'POST', $payload);
        $request->setContainer($this->app);
        $request->setRedirector($this->app['redirect']);
        $request->setUserResolver(fn (): User => $user);

        return $request;
    }

    private function validator(FormRequest $request): \Illuminate\Contracts\Validation\Validator
    {
        return Validator::make(
            $request->all(),
            $request->rules(),
            method_exists($request, 'messages') ? $request->messages() : [],
            method_exists($request, 'attributes') ? $request->attributes() : [],
        );
    }

    private function assertTranslatedFailure(\Illuminate\Contracts\Validation\Validator $validator, string $field, string $key): void
    {
        $this->assertTrue($validator->fails());
        $this->assertSame(tkey($key), $validator->errors()->first($field));
        $this->assertNotSame($key, tkey($key));
    }

    /**
     * @param  array<int, string>  $permissions
     */
    private function analyticsUser(array $permissions): User
    {
        $user = User::factory()->create();
        $user->forceFill([
            'permissions' => collect($permissions)->mapWithKeys(fn (string $permission): array => [$permission => true])->all(),
        ])->save();

        return $user;
    }
}
