<?php

namespace Tests\Feature;

use App\Actions\Analytics\ClearAnalyticsCacheAction;
use App\Actions\Analytics\GetAnalyticsCacheEntryAction;
use App\Actions\Analytics\PutAnalyticsCacheEntryAction;
use App\Actions\Analytics\StoreAnalyticsSnapshotAction;
use App\Enums\AnalyticsSnapshotType;
use App\Enums\KpiPeriod;
use App\Models\AnalyticsCacheEntry;
use App\Models\AnalyticsSnapshot;
use App\Models\Branch;
use App\Models\User;
use App\Rules\AnalyticsCacheKeyRule;
use App\Rules\AnalyticsDateRangeRule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class AnalyticsSnapshotsCacheTest extends TestCase
{
    use RefreshDatabase;

    public function test_snapshot_and_cache_entry_schema_match_local_contract(): void
    {
        $columns = [
            'analytics_snapshots' => [
                'id',
                'uuid',
                'snapshot_type',
                'period_type',
                'period_start',
                'period_end',
                'branch_id',
                'user_id',
                'data',
                'calculated_at',
                'metadata',
                'created_at',
                'updated_at',
            ],
            'analytics_cache_entries' => [
                'id',
                'cache_key',
                'data',
                'tags',
                'expires_at',
                'calculated_at',
                'created_at',
                'updated_at',
            ],
        ];

        foreach ($columns as $table => $expectedColumns) {
            $this->assertTrue(Schema::hasTable($table), $table);
            $this->assertEmpty(array_intersect(
                ['tenant_id', 'company_id', 'subscription_id', 'reseller_id', 'platform_owner_id', 'telemetry_id'],
                Schema::getColumnListing($table),
            ), $table);

            foreach ($expectedColumns as $column) {
                $this->assertTrue(Schema::hasColumn($table, $column), $table.'.'.$column);
            }
        }
    }

    public function test_snapshot_can_be_stored(): void
    {
        $branch = Branch::factory()->create();
        $user = User::factory()->create();

        $snapshot = app(StoreAnalyticsSnapshotAction::class)->handle(
            AnalyticsSnapshotType::OwnerDashboard,
            KpiPeriod::Month,
            '2026-05-01',
            '2026-05-31',
            ['open_leads' => 7],
            $branch,
            $user,
            ['source' => 'test'],
            now()->setTime(9, 0),
        );

        $this->assertNotEmpty($snapshot->uuid);
        $this->assertSame(AnalyticsSnapshotType::OwnerDashboard, $snapshot->snapshot_type);
        $this->assertSame(KpiPeriod::Month, $snapshot->period_type);
        $this->assertSame('2026-05-01', $snapshot->period_start->toDateString());
        $this->assertSame('2026-05-31', $snapshot->period_end->toDateString());
        $this->assertSame(['open_leads' => 7], $snapshot->data);
        $this->assertSame(['source' => 'test'], $snapshot->metadata);
        $this->assertTrue($snapshot->branch->is($branch));
        $this->assertTrue($snapshot->user->is($user));
        $this->assertTrue(
            AnalyticsSnapshot::query()
                ->ofType(AnalyticsSnapshotType::OwnerDashboard)
                ->forPeriodType(KpiPeriod::Month)
                ->forBranch($branch)
                ->forUser($user)
                ->latestSnapshots()
                ->firstOrFail()
                ->is($snapshot),
        );
    }

    public function test_cache_entry_can_be_stored_and_expired_cache_is_ignored(): void
    {
        $cache = app(PutAnalyticsCacheEntryAction::class)->handle(
            'owner_dashboard.metrics',
            ['open_leads' => 3],
            ['analytics', 'dashboard'],
            now()->addMinutes(10),
        );

        $this->assertSame(['open_leads' => 3], $cache->data);
        $this->assertSame(['analytics', 'dashboard'], $cache->tags);
        $this->assertTrue($cache->isFresh());
        $this->assertTrue(app(GetAnalyticsCacheEntryAction::class)->handle('owner_dashboard.metrics')->is($cache));

        app(PutAnalyticsCacheEntryAction::class)->handle(
            'owner_dashboard.metrics',
            ['open_leads' => 4],
            ['analytics', 'dashboard'],
            now()->subMinute(),
        );

        $this->assertNull(app(GetAnalyticsCacheEntryAction::class)->handle('owner_dashboard.metrics'));
        $this->assertTrue(AnalyticsCacheEntry::query()->forKey('owner_dashboard.metrics')->firstOrFail()->isExpired());
    }

    public function test_cache_can_be_cleared(): void
    {
        $dashboard = AnalyticsCacheEntry::factory()->key('owner_dashboard.metrics')->tagged(['analytics', 'dashboard'])->create();
        $reports = AnalyticsCacheEntry::factory()->key('reports.sales')->tagged(['analytics', 'reports'])->create();
        $other = AnalyticsCacheEntry::factory()->key('public.site')->tagged(['public'])->create();

        $this->assertSame(2, app(ClearAnalyticsCacheAction::class)->handle(['analytics']));
        $this->assertDatabaseMissing('analytics_cache_entries', ['id' => $dashboard->id]);
        $this->assertDatabaseMissing('analytics_cache_entries', ['id' => $reports->id]);
        $this->assertDatabaseHas('analytics_cache_entries', ['id' => $other->id]);

        $this->assertSame(1, app(ClearAnalyticsCacheAction::class)->handle(key: 'public.site'));
        $this->assertDatabaseCount('analytics_cache_entries', 0);
    }

    public function test_cache_key_and_date_range_validation_work(): void
    {
        $validKey = Validator::make(['cache_key' => 'owner_dashboard.metrics'], [
            'cache_key' => [new AnalyticsCacheKeyRule],
        ]);
        $this->assertFalse($validKey->fails());

        $invalidKey = Validator::make(['cache_key' => 'Owner Dashboard Metrics'], [
            'cache_key' => [new AnalyticsCacheKeyRule],
        ]);
        $this->assertTrue($invalidKey->fails());
        $this->assertSame(tkey('analytics.validation.invalid_cache_key'), $invalidKey->errors()->first('cache_key'));

        $dateRange = Validator::make([
            'filters' => [
                'period_start' => '2026-06-01',
                'period_end' => '2026-05-01',
            ],
        ], [
            'filters.period_start' => [new AnalyticsDateRangeRule],
        ]);

        $this->assertTrue($dateRange->fails());
        $this->assertSame(tkey('analytics.validation.invalid_date_range'), $dateRange->errors()->first('filters.period_start'));
    }
}
