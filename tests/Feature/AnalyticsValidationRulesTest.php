<?php

namespace Tests\Feature;

use App\Enums\AnalyticsRunStatus;
use App\Enums\KpiPeriod;
use App\Enums\ReportExportFormat;
use App\Models\KpiMetric;
use App\Models\KpiTarget;
use App\Models\ReportDefinition;
use App\Models\ReportRun;
use App\Models\User;
use App\Rules\ActiveKpiMetricRule;
use App\Rules\ActiveReportDefinitionRule;
use App\Rules\AnalyticsCacheKeyRule;
use App\Rules\AnalyticsDateRangeRule;
use App\Rules\AnalyticsModuleAvailableRule;
use App\Rules\AnalyticsPermissionRule;
use App\Rules\DashboardWidgetConfigRule;
use App\Rules\KpiTargetUniquenessRule;
use App\Rules\KpiTargetValueRule;
use App\Rules\ReportColumnAllowedRule;
use App\Rules\ReportExportAllowedRule;
use App\Rules\ReportFilterValueAllowedRule;
use App\Rules\ValidKpiPeriodRule;
use App\Rules\ValidReportFilterRule;
use App\Rules\ValidReportFormatRule;
use Database\Seeders\AnalyticsTranslationSeeder;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class AnalyticsValidationRulesTest extends TestCase
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

    public function test_report_validation_rules_accept_valid_data_and_return_translated_errors(): void
    {
        $definition = ReportDefinition::factory()->create([
            'code' => 'lead_pipeline_validation',
            'default_filters' => ['status' => null],
            'column_config' => ['lead_name' => ['label' => 'Lead']],
            'is_active' => true,
        ]);
        $inactiveDefinition = ReportDefinition::factory()->inactive()->create();
        $user = $this->analyticsUser(['analytics.reports.export']);
        $completedRun = ReportRun::factory()->forDefinition($definition)->create([
            'status' => AnalyticsRunStatus::Completed,
        ]);
        $runningRun = ReportRun::factory()->forDefinition($definition)->create([
            'status' => AnalyticsRunStatus::Running,
        ]);

        $this->assertRulePasses('report', $definition->id, new ActiveReportDefinitionRule);
        $this->assertRuleFailsWith('report', $inactiveDefinition->id, new ActiveReportDefinitionRule, 'analytics.validation.report_not_active');

        $this->assertRulePasses('filters', ['status' => 'new'], new ValidReportFilterRule($definition));
        $this->assertRuleFailsWith('filters', ['tenant_id' => 1], new ValidReportFilterRule($definition), 'analytics.validation.invalid_filter');

        $this->assertRulePasses('format', ReportExportFormat::Csv->value, new ValidReportFormatRule);
        $this->assertRuleFailsWith('format', 'csv', new ValidReportFormatRule, 'analytics.validation.invalid_format');

        $this->assertRulePasses('format', ReportExportFormat::Json->value, new ReportExportAllowedRule($completedRun, $definition, $user));
        $this->assertRuleFailsWith('format', ReportExportFormat::Json->value, new ReportExportAllowedRule($runningRun, $definition, $user), 'analytics.validation.export_not_allowed');

        $this->assertRulePasses('column', 'lead_name', new ReportColumnAllowedRule($definition));
        $this->assertRuleFailsWith('column', 'tenant_id', new ReportColumnAllowedRule($definition), 'analytics.validation.column_not_allowed');

        $this->assertRulePasses('status', 'new', new ReportFilterValueAllowedRule(allowedValues: ['new', 'converted']));
        $this->assertRuleFailsWith('status', 'archived', new ReportFilterValueAllowedRule(allowedValues: ['new', 'converted']), 'analytics.validation.filter_value_not_allowed');
    }

    public function test_kpi_validation_rules_accept_valid_data_and_return_translated_errors(): void
    {
        $metric = KpiMetric::factory()->create(['is_active' => true]);
        $inactiveMetric = KpiMetric::factory()->inactive()->create();
        $periodStart = now()->startOfMonth()->toDateString();

        KpiTarget::factory()
            ->forMetric($metric)
            ->period(KpiPeriod::Month, $periodStart)
            ->create();

        $this->assertRulePasses('metric', $metric->id, new ActiveKpiMetricRule);
        $this->assertRuleFailsWith('metric', $inactiveMetric->id, new ActiveKpiMetricRule, 'analytics.validation.kpi_not_active');

        $this->assertRulePasses('period', KpiPeriod::Month->value, new ValidKpiPeriodRule);
        $this->assertRuleFailsWith('period', 'semester', new ValidKpiPeriodRule, 'analytics.validation.invalid_period');

        $this->assertRulePasses('target_value', '100.2500', new KpiTargetValueRule);
        $this->assertRuleFailsWith('target_value', '-1', new KpiTargetValueRule, 'analytics.validation.invalid_target_value');

        $valid = Validator::make([
            'target' => [
                'kpi_metric_id' => $metric->id,
                'period_type' => KpiPeriod::Month->value,
                'period_start' => now()->addMonth()->startOfMonth()->toDateString(),
            ],
        ], [
            'target.kpi_metric_id' => [new KpiTargetUniquenessRule],
        ]);
        $this->assertFalse($valid->fails(), json_encode($valid->errors()->toArray(), JSON_THROW_ON_ERROR));

        $invalid = Validator::make([
            'target' => [
                'kpi_metric_id' => $metric->id,
                'period_type' => KpiPeriod::Month->value,
                'period_start' => $periodStart,
            ],
        ], [
            'target.kpi_metric_id' => [new KpiTargetUniquenessRule],
        ]);
        $this->assertTranslatedFailure($invalid, 'target.kpi_metric_id', 'analytics.validation.duplicate_kpi_target');
    }

    public function test_dashboard_cache_permission_module_and_date_rules_return_translated_errors(): void
    {
        $user = $this->analyticsUser(['analytics.dashboard.view']);
        $deniedUser = User::factory()->create();

        $this->assertRulePasses('config', ['metric' => 'open_leads', 'type' => 'counter', 'columns' => ['status']], new DashboardWidgetConfigRule);
        $this->assertRuleFailsWith('config', ['tenant_id' => 1], new DashboardWidgetConfigRule, 'analytics.validation.invalid_widget_config');

        $this->assertRulePasses('cache_key', 'owner_dashboard.metrics', new AnalyticsCacheKeyRule);
        $this->assertRuleFailsWith('cache_key', 'Bad Key!', new AnalyticsCacheKeyRule, 'analytics.validation.invalid_cache_key');

        $this->assertRulePasses('permission', 'analytics.dashboard.view', new AnalyticsPermissionRule($user));
        $this->assertRuleFailsWith('permission', 'analytics.dashboard.view', new AnalyticsPermissionRule($deniedUser), 'analytics.validation.permission_denied');

        $this->assertRulePasses('module', 'students', new AnalyticsModuleAvailableRule);
        $this->assertRuleFailsWith('module', 'missing_module', new AnalyticsModuleAvailableRule, 'analytics.validation.module_not_available');

        $validRange = Validator::make([
            'filters' => ['period_start' => '2026-01-01', 'period_end' => '2026-01-31'],
        ], [
            'filters.period_start' => [new AnalyticsDateRangeRule],
        ]);
        $this->assertFalse($validRange->fails(), json_encode($validRange->errors()->toArray(), JSON_THROW_ON_ERROR));

        $invalidRange = Validator::make([
            'filters' => ['period_start' => '2026-02-01', 'period_end' => '2026-01-31'],
        ], [
            'filters.period_start' => [new AnalyticsDateRangeRule],
        ]);
        $this->assertTranslatedFailure($invalidRange, 'filters.period_start', 'analytics.validation.invalid_date_range');
    }

    private function assertRulePasses(string $field, mixed $value, ValidationRule $rule): void
    {
        $validator = Validator::make([$field => $value], [$field => [$rule]]);

        $this->assertFalse($validator->fails(), json_encode($validator->errors()->toArray(), JSON_THROW_ON_ERROR));
    }

    private function assertRuleFailsWith(string $field, mixed $value, ValidationRule $rule, string $key): void
    {
        $validator = Validator::make([$field => $value], [$field => [$rule]]);

        $this->assertTranslatedFailure($validator, $field, $key);
    }

    private function assertTranslatedFailure(mixed $validator, string $field, string $key): void
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
