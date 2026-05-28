<?php

namespace Tests\Feature;

use App\Actions\Analytics\CalculateDashboardSummaryAction;
use App\Actions\Analytics\CalculateKpiMetricAction;
use App\Actions\Analytics\CalculateKpiSnapshotAction;
use App\Actions\Analytics\ExportReportAsCommaSeparatedValuesAction;
use App\Actions\Analytics\ExportReportAsJsonAction;
use App\Actions\Analytics\ExportReportAsSpreadsheetPlaceholderAction;
use App\Actions\Analytics\RefreshAnalyticsCacheAction;
use App\Actions\Analytics\RunReportAction;
use App\Enums\KpiMetricGroup;
use App\Enums\KpiPeriod;
use App\Enums\KpiSnapshotStatus;
use App\Enums\KpiUnit;
use App\Enums\LeadStatus;
use App\Enums\ReportExportFormat;
use App\Models\AnalyticsCache;
use App\Models\AnalyticsCacheEntry;
use App\Models\KpiMetric;
use App\Models\KpiSnapshot;
use App\Models\KpiTarget;
use App\Models\MarketingLead;
use App\Models\ReportDefinition;
use App\Models\ReportExport;
use App\Models\ReportRun;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AnalyticsCoreActionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_summary_action_counts_metrics_and_handles_missing_optional_module_tables(): void
    {
        $user = $this->analyticsUser(['analytics.dashboard.view']);

        MarketingLead::factory()->count(2)->create(['status' => LeadStatus::New]);
        MarketingLead::factory()->create(['status' => LeadStatus::BecameStudent, 'converted_at' => now()]);

        $summary = app(CalculateDashboardSummaryAction::class)->handle([], $user);

        $this->assertSame(2, $summary['metrics']['open_leads']);
        $this->assertSame(1, $summary['metrics']['converted_leads']);
        $this->assertTrue($summary['modules']['notifications']);

        Schema::dropIfExists('notification_delivery_logs');

        $summary = app(CalculateDashboardSummaryAction::class)->handle([], $user);

        $this->assertSame(0, $summary['metrics']['queued_notifications']);
        $this->assertFalse($summary['modules']['notifications']);
        $this->assertContains('notifications', $summary['missing_modules']);
    }

    public function test_report_run_action_creates_completed_run_without_full_table_iteration(): void
    {
        $user = $this->analyticsUser(['analytics.reports.run']);
        $definition = ReportDefinition::factory()->create([
            'code' => 'crm_lead_pipeline',
            'is_active' => true,
        ]);
        MarketingLead::factory()->count(3)->create(['status' => LeadStatus::New]);

        $result = app(RunReportAction::class)->handle($definition, [], $user);

        $this->assertInstanceOf(ReportRun::class, $result['run']);
        $this->assertSame(3, $result['summary']['row_count']);
        $this->assertSame(3, $result['summary']['by_status'][LeadStatus::New->value]);
        $this->assertDatabaseHas('report_runs', [
            'id' => $result['run']->id,
            'status' => 'completed',
            'row_count' => 3,
        ]);
    }

    public function test_report_export_actions_create_export_records_and_payloads(): void
    {
        $user = $this->analyticsUser(['analytics.reports.export']);
        $run = ReportRun::factory()->create([
            'summary' => ['row_count' => 2, 'paid_revenue_cents' => 10000],
            'row_count' => 2,
        ]);

        $csv = app(ExportReportAsCommaSeparatedValuesAction::class)->handle($run, $user);
        $json = app(ExportReportAsJsonAction::class)->handle($run, $user);
        $spreadsheet = app(ExportReportAsSpreadsheetPlaceholderAction::class)->handle($run, $user);

        $this->assertInstanceOf(ReportExport::class, $csv['export']);
        $this->assertStringContainsString('summary.row_count', $csv['content']);
        $this->assertJson($json['content']);
        $this->assertStringContainsString(ReportExportFormat::SpreadsheetPlaceholder->value, $spreadsheet['content']);
        $this->assertDatabaseCount('report_exports', 3);
    }

    public function test_key_performance_indicator_metric_and_snapshot_can_be_calculated(): void
    {
        $metric = KpiMetric::factory()
            ->group(KpiMetricGroup::Sales)
            ->unit(KpiUnit::Count)
            ->create([
                'code' => 'open_leads',
                'is_active' => true,
            ]);
        KpiTarget::factory()
            ->forMetric($metric)
            ->period(KpiPeriod::Month, now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString())
            ->create([
                'target_value' => 2,
                'warning_threshold' => 1,
                'success_threshold' => 3,
                'warning_value' => 1,
            ]);
        MarketingLead::factory()->count(2)->create(['status' => LeadStatus::New]);

        $calculation = app(CalculateKpiMetricAction::class)->handle($metric);
        $snapshotResult = app(CalculateKpiSnapshotAction::class)->handle($metric, KpiPeriod::Month);

        $this->assertSame(2.0, $calculation['value']);
        $this->assertInstanceOf(KpiSnapshot::class, $snapshotResult['snapshot']);
        $this->assertSame(KpiSnapshotStatus::Achieved, $snapshotResult['snapshot']->status);
        $this->assertSame(KpiSnapshotStatus::Achieved, $snapshotResult['comparison']['status']);
    }

    public function test_cache_refresh_updates_legacy_and_cache_entry_storage(): void
    {
        $cache = app(RefreshAnalyticsCacheAction::class)->handle(
            'owner_dashboard.metrics',
            ['open_leads' => 5],
            'dashboard',
            10,
            ['analytics', 'dashboard'],
        );

        $this->assertInstanceOf(AnalyticsCache::class, $cache);
        $this->assertDatabaseHas('analytics_cache', ['key' => 'owner_dashboard.metrics']);
        $this->assertTrue(AnalyticsCacheEntry::query()->forKey('owner_dashboard.metrics')->fresh()->exists());
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
