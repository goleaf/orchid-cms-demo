<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->createDashboardWidgetsTable();
        $this->createReportDefinitionsTable();
        $this->createReportRunsTable();
        $this->createReportExportsTable();
        $this->createKpiMetricsTable();
        $this->createKpiTargetsTable();
        $this->createKpiSnapshotsTable();
        $this->createAnalyticsCacheTable();
        $this->createUserDashboardPreferencesTable();
    }

    public function down(): void
    {
        Schema::dropIfExists('user_dashboard_preferences');
        Schema::dropIfExists('analytics_cache');
        Schema::dropIfExists('kpi_snapshots');
        Schema::dropIfExists('kpi_targets');
        Schema::dropIfExists('kpi_metrics');
        Schema::dropIfExists('report_exports');
        Schema::dropIfExists('report_runs');
        Schema::dropIfExists('report_definitions');
        Schema::dropIfExists('dashboard_widgets');
    }

    private function createDashboardWidgetsTable(): void
    {
        Schema::create('dashboard_widgets', function (Blueprint $table): void {
            $table->id();
            $table->string('code')->unique();
            $table->json('name_translations')->nullable();
            $table->json('description_translations')->nullable();
            $table->string('widget_type')->default('metric')->index();
            $table->string('metric_code')->nullable()->index();
            $table->string('component')->nullable();
            $table->boolean('is_system')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->json('settings')->nullable();
            $table->foreignId('created_by_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('updated_by_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
            $table->index(['widget_type', 'is_active']);
        });
    }

    private function createReportDefinitionsTable(): void
    {
        Schema::create('report_definitions', function (Blueprint $table): void {
            $table->id();
            $table->string('code')->unique();
            $table->json('name_translations')->nullable();
            $table->json('description_translations')->nullable();
            $table->string('report_type')->default('operational')->index();
            $table->string('source_model')->nullable();
            $table->json('default_filters')->nullable();
            $table->json('column_config')->nullable();
            $table->json('schedule')->nullable();
            $table->boolean('is_system')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->foreignId('created_by_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('updated_by_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->timestamps();

            $table->index(['report_type', 'is_active', 'sort_order']);
        });
    }

    private function createReportRunsTable(): void
    {
        Schema::create('report_runs', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->nullable()->unique();
            $table->foreignId('report_definition_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('status')->default('pending')->index();
            $table->date('period_start')->nullable()->index();
            $table->date('period_end')->nullable()->index();
            $table->timestamp('started_at')->nullable()->index();
            $table->timestamp('finished_at')->nullable()->index();
            $table->unsignedInteger('row_count')->default(0);
            $table->json('filters')->nullable();
            $table->json('summary')->nullable();
            $table->json('result_payload')->nullable();
            $table->text('error_message')->nullable();
            $table->foreignId('created_by_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->timestamps();

            $table->index(['report_definition_id', 'status'], 'report_runs_definition_status_idx');
            $table->index(['report_definition_id', 'period_start', 'period_end'], 'report_runs_definition_period_idx');
        });
    }

    private function createReportExportsTable(): void
    {
        Schema::create('report_exports', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->nullable()->unique();
            $table->foreignId('report_definition_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('report_run_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->string('format')->default('csv')->index();
            $table->string('status')->default('pending')->index();
            $table->string('file_name')->nullable();
            $table->string('disk')->default('local');
            $table->string('path')->nullable();
            $table->unsignedInteger('row_count')->default(0);
            $table->json('filters')->nullable();
            $table->timestamp('exported_at')->nullable()->index();
            $table->timestamp('expires_at')->nullable()->index();
            $table->text('error_message')->nullable();
            $table->foreignId('created_by_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->timestamps();

            $table->index(['report_definition_id', 'format', 'status'], 'report_exports_definition_format_status_idx');
        });
    }

    private function createKpiMetricsTable(): void
    {
        Schema::create('kpi_metrics', function (Blueprint $table): void {
            $table->id();
            $table->string('code')->unique();
            $table->json('name_translations')->nullable();
            $table->json('description_translations')->nullable();
            $table->string('category')->default('operations')->index();
            $table->string('value_type')->default('number')->index();
            $table->string('unit')->nullable();
            $table->string('calculation')->nullable();
            $table->string('source')->nullable();
            $table->boolean('is_system')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->json('settings')->nullable();
            $table->foreignId('created_by_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('updated_by_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->timestamps();

            $table->index(['category', 'is_active', 'sort_order']);
        });
    }

    private function createKpiTargetsTable(): void
    {
        Schema::create('kpi_targets', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('kpi_metric_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('period')->default('month')->index();
            $table->date('starts_on')->index();
            $table->date('ends_on')->nullable()->index();
            $table->decimal('target_value', 14, 4);
            $table->decimal('warning_value', 14, 4)->nullable();
            $table->string('direction')->default('increase')->index();
            $table->foreignId('branch_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('training_program_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('training_group_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('assigned_to_user_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->json('metadata')->nullable();
            $table->foreignId('created_by_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('updated_by_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->timestamps();

            $table->index(['kpi_metric_id', 'period', 'starts_on'], 'kpi_targets_metric_period_start_idx');
            $table->index(['branch_id', 'period', 'starts_on'], 'kpi_targets_branch_period_start_idx');
        });
    }

    private function createKpiSnapshotsTable(): void
    {
        Schema::create('kpi_snapshots', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('kpi_metric_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('period')->default('day')->index();
            $table->date('snapshot_date')->index();
            $table->decimal('value', 14, 4);
            $table->decimal('target_value', 14, 4)->nullable();
            $table->string('status')->default('neutral')->index();
            $table->foreignId('branch_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('training_program_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('training_group_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->json('source_payload')->nullable();
            $table->timestamp('calculated_at')->nullable()->index();
            $table->timestamps();

            $table->index(['kpi_metric_id', 'snapshot_date', 'period'], 'kpi_snapshots_metric_date_period_idx');
            $table->index(['branch_id', 'snapshot_date'], 'kpi_snapshots_branch_date_idx');
        });
    }

    private function createAnalyticsCacheTable(): void
    {
        Schema::create('analytics_cache', function (Blueprint $table): void {
            $table->id();
            $table->string('key')->unique();
            $table->string('group')->default('analytics')->index();
            $table->json('value')->nullable();
            $table->json('tags')->nullable();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamp('refreshed_at')->nullable()->index();
            $table->foreignId('created_by_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->timestamps();

            $table->index(['group', 'expires_at']);
        });
    }

    private function createUserDashboardPreferencesTable(): void
    {
        Schema::create('user_dashboard_preferences', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('dashboard')->default('owner')->index();
            $table->json('visible_widget_codes')->nullable();
            $table->json('widget_order')->nullable();
            $table->json('filters')->nullable();
            $table->unsignedSmallInteger('refresh_interval_seconds')->default(300);
            $table->string('timezone')->nullable();
            $table->boolean('is_default')->default(false);
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'dashboard'], 'user_dashboard_preferences_user_dashboard_unique');
        });
    }
};
