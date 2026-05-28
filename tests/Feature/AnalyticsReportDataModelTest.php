<?php

namespace Tests\Feature;

use App\Enums\AnalyticsRunStatus;
use App\Enums\ReportExportFormat;
use App\Enums\ReportGroup;
use App\Models\ReportDefinition;
use App\Models\ReportExport;
use App\Models\ReportRun;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AnalyticsReportDataModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_report_schema_matches_local_analytics_contract(): void
    {
        $columns = [
            'report_definitions' => [
                'id',
                'uuid',
                'code',
                'name_translations',
                'description_translations',
                'report_group',
                'data_source',
                'filters_schema',
                'columns_schema',
                'permissions',
                'is_active',
                'is_system',
                'sort_order',
                'created_by_id',
                'updated_by_id',
                'deleted_at',
                'created_at',
                'updated_at',
            ],
            'report_runs' => [
                'id',
                'uuid',
                'report_definition_id',
                'user_id',
                'status',
                'filters',
                'started_at',
                'finished_at',
                'row_count',
                'error_message',
                'metadata',
                'created_at',
                'updated_at',
            ],
            'report_exports' => [
                'id',
                'uuid',
                'report_run_id',
                'format',
                'disk',
                'path',
                'filename',
                'mime_type',
                'size_bytes',
                'exported_by_id',
                'exported_at',
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
        $definition = ReportDefinition::factory()
            ->createdBy($user)
            ->group(ReportGroup::Finance)
            ->dataSource('App\\Models\\Payment')
            ->create([
                'code' => 'finance_monthly_summary',
                'name_translations' => ['en' => 'Finance monthly summary', 'ru' => 'Финансовый отчет'],
                'description_translations' => ['en' => 'Monthly payment overview'],
                'filters_schema' => ['period_start' => ['type' => 'date']],
                'columns_schema' => ['amount_cents' => ['type' => 'money']],
                'permissions' => ['analytics.reports.export'],
            ]);
        $inactiveDefinition = ReportDefinition::factory()->inactive()->create();

        $run = ReportRun::factory()
            ->forDefinition($definition)
            ->byUser($user)
            ->status(AnalyticsRunStatus::Completed)
            ->create([
                'started_at' => now()->subSeconds(15),
                'finished_at' => now(),
                'filters' => ['period' => 'month'],
                'metadata' => ['manual' => true],
            ]);
        $export = ReportExport::factory()
            ->forRun($run)
            ->createdBy($user)
            ->format(ReportExportFormat::SpreadsheetPlaceholder)
            ->create([
                'filename' => 'finance-summary.xlsx',
                'file_name' => 'legacy-finance-summary.xlsx',
                'size_bytes' => 2048,
                'metadata' => ['placeholder' => true],
            ]);

        $this->assertNotEmpty($definition->uuid);
        $this->assertSame('Finance monthly summary', $definition->displayName('en'));
        $this->assertSame('Monthly payment overview', $definition->displayDescription('en'));
        $this->assertSame('App\\Models\\Payment', $definition->dataSource());
        $this->assertSame(['analytics.reports.export'], $definition->requiredPermissions());
        $this->assertTrue(ReportDefinition::query()->active()->forGroup(ReportGroup::Finance)->firstOrFail()->is($definition));
        $this->assertFalse(ReportDefinition::query()->active()->whereKey($inactiveDefinition->id)->exists());
        $this->assertTrue($definition->runs->first()->is($run));
        $this->assertTrue($definition->exports->first()->is($export));

        $this->assertTrue($run->definition->is($definition));
        $this->assertTrue($run->user->is($user));
        $this->assertTrue($run->creator->is($user));
        $this->assertTrue($run->exports->first()->is($export));
        $this->assertTrue($run->isFinished());
        $this->assertFalse($run->hasFailed());
        $this->assertSame(15, $run->durationInSeconds());
        $this->assertTrue(ReportRun::query()->forDefinition($definition)->forUser($user)->completed()->firstOrFail()->is($run));

        $this->assertTrue($export->definition->is($definition));
        $this->assertTrue($export->run->is($run));
        $this->assertTrue($export->creator->is($user));
        $this->assertTrue($export->exportedBy->is($user));
        $this->assertSame('finance-summary.xlsx', $export->displayFilename());
        $this->assertSame('2 KB', $export->sizeForHumans());
        $this->assertTrue(ReportExport::query()->forRun($run)->format(ReportExportFormat::SpreadsheetPlaceholder)->firstOrFail()->is($export));
    }

    public function test_report_group_status_and_export_format_values_match_the_local_contract(): void
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
            'system',
        ], ReportGroup::values());

        $this->assertSame([
            'pending',
            'running',
            'completed',
            'failed',
            'cancelled',
        ], AnalyticsRunStatus::values());

        $this->assertSame([
            'comma_separated_values',
            'spreadsheet_placeholder',
            'json',
        ], ReportExportFormat::values());
    }
}
