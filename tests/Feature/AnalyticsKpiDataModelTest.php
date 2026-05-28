<?php

namespace Tests\Feature;

use App\Enums\KpiMetricGroup;
use App\Enums\KpiPeriod;
use App\Enums\KpiSnapshotStatus;
use App\Enums\KpiUnit;
use App\Models\Branch;
use App\Models\KpiMetric;
use App\Models\KpiSnapshot;
use App\Models\KpiTarget;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AnalyticsKpiDataModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_kpi_schema_matches_local_analytics_contract(): void
    {
        $columns = [
            'kpi_metrics' => [
                'id',
                'uuid',
                'code',
                'name_translations',
                'description_translations',
                'metric_group',
                'unit',
                'calculation_type',
                'is_active',
                'is_system',
                'sort_order',
                'created_by_id',
                'updated_by_id',
                'deleted_at',
                'created_at',
                'updated_at',
            ],
            'kpi_targets' => [
                'id',
                'uuid',
                'kpi_metric_id',
                'branch_id',
                'user_id',
                'period_type',
                'period_start',
                'period_end',
                'target_value',
                'warning_threshold',
                'success_threshold',
                'created_by_id',
                'updated_by_id',
                'deleted_at',
                'created_at',
                'updated_at',
            ],
            'kpi_snapshots' => [
                'id',
                'uuid',
                'kpi_metric_id',
                'branch_id',
                'user_id',
                'period_type',
                'period_start',
                'period_end',
                'value',
                'target_value',
                'status',
                'calculated_at',
                'metadata',
                'created_at',
                'updated_at',
            ],
        ];

        foreach ($columns as $table => $expectedColumns) {
            $this->assertTrue(Schema::hasTable($table), $table);
            $this->assertEmpty(array_intersect(
                ['tenant_id', 'company_id', 'subscription_id', 'reseller_id', 'platform_owner_id'],
                Schema::getColumnListing($table),
            ), $table);

            foreach ($expectedColumns as $column) {
                $this->assertTrue(Schema::hasColumn($table, $column), $table.'.'.$column);
            }
        }
    }

    public function test_models_factories_relationships_scopes_and_helpers_work(): void
    {
        $user = User::factory()->create();
        $branch = Branch::factory()->create();
        $metric = KpiMetric::factory()
            ->createdBy($user)
            ->group(KpiMetricGroup::Finance)
            ->unit(KpiUnit::Money)
            ->calculationType('sum_paid_payments')
            ->create([
                'code' => 'monthly_paid_revenue',
                'name_translations' => ['en' => 'Monthly paid revenue', 'ru' => 'Оплаченная выручка'],
                'description_translations' => ['en' => 'Paid revenue for a month'],
            ]);
        $inactiveMetric = KpiMetric::factory()->inactive()->create();

        $target = KpiTarget::factory()
            ->forMetric($metric)
            ->scopedTo($branch)
            ->assignedTo($user)
            ->period(KpiPeriod::Month, '2026-05-01', '2026-05-31')
            ->create([
                'target_value' => 10000,
                'warning_threshold' => 8000,
                'success_threshold' => 12000,
            ]);
        $snapshot = KpiSnapshot::factory()
            ->forMetric($metric)
            ->scopedTo($branch)
            ->forUser($user)
            ->period(KpiPeriod::Month, '2026-05-01', '2026-05-31')
            ->create([
                'value' => 12500,
                'target_value' => 10000,
                'status' => KpiSnapshotStatus::Exceeded,
                'metadata' => ['source' => 'test'],
            ]);

        $this->assertNotEmpty($metric->uuid);
        $this->assertSame('Monthly paid revenue', $metric->displayName('en'));
        $this->assertSame('Paid revenue for a month', $metric->displayDescription('en'));
        $this->assertSame('finance', $metric->groupValue());
        $this->assertSame('sum_paid_payments', $metric->calculationType());
        $this->assertTrue(KpiMetric::query()->active()->forGroup(KpiMetricGroup::Finance)->firstOrFail()->is($metric));
        $this->assertFalse(KpiMetric::query()->active()->whereKey($inactiveMetric->id)->exists());
        $this->assertTrue($metric->targets->first()->is($target));
        $this->assertTrue($metric->snapshots->first()->is($snapshot));

        $this->assertTrue($target->metric->is($metric));
        $this->assertTrue($target->branch->is($branch));
        $this->assertTrue($target->user->is($user));
        $this->assertTrue($target->assignee->is($user));
        $this->assertTrue($target->creator->is($user));
        $this->assertSame(KpiPeriod::Month, $target->periodType());
        $this->assertSame('2026-05-01', $target->periodStart()?->toDateString());
        $this->assertSame(8000.0, (float) $target->warningThreshold());
        $this->assertSame(12000.0, (float) $target->successThreshold());
        $this->assertTrue(KpiTarget::query()->forMetric($metric)->forBranch($branch)->forUser($user)->forPeriodType(KpiPeriod::Month)->firstOrFail()->is($target));

        $this->assertTrue($snapshot->metric->is($metric));
        $this->assertTrue($snapshot->branch->is($branch));
        $this->assertTrue($snapshot->user->is($user));
        $this->assertSame(KpiPeriod::Month, $snapshot->periodType());
        $this->assertSame('2026-05-01', $snapshot->periodStart()?->toDateString());
        $this->assertTrue($snapshot->isSuccessful());
        $this->assertSame(['source' => 'test'], $snapshot->metadata);
        $this->assertTrue(KpiSnapshot::query()->forMetric($metric)->forBranch($branch)->forUser($user)->forPeriodType(KpiPeriod::Month)->latestSnapshots()->firstOrFail()->is($snapshot));
    }

    public function test_kpi_metric_group_unit_period_and_snapshot_status_values_match_contract(): void
    {
        $this->assertSame([
            'sales',
            'finance',
            'students',
            'education',
            'lessons',
            'driving',
            'documents',
            'exams',
            'notifications',
            'staff',
        ], KpiMetricGroup::values());

        $this->assertSame([
            'count',
            'percent',
            'money',
            'hours',
            'days',
            'ratio',
        ], KpiUnit::values());

        $this->assertSame([
            'day',
            'week',
            'month',
            'quarter',
            'year',
            'custom',
        ], KpiPeriod::values());

        $this->assertSame([
            'below_target',
            'on_track',
            'achieved',
            'exceeded',
            'unknown',
        ], KpiSnapshotStatus::values());
    }
}
