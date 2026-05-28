<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->extendKpiMetricsTable();
        $this->extendKpiTargetsTable();
        $this->extendKpiSnapshotsTable();
    }

    public function down(): void
    {
        $this->revertKpiSnapshotsTable();
        $this->revertKpiTargetsTable();
        $this->revertKpiMetricsTable();
    }

    private function extendKpiMetricsTable(): void
    {
        if (! Schema::hasTable('kpi_metrics')) {
            return;
        }

        Schema::table('kpi_metrics', function (Blueprint $table): void {
            if (! Schema::hasColumn('kpi_metrics', 'uuid')) {
                $table->uuid('uuid')->nullable()->unique()->after('id');
            }

            if (! Schema::hasColumn('kpi_metrics', 'metric_group')) {
                $table->string('metric_group')->default('staff')->index()->after('description_translations');
            }

            if (! Schema::hasColumn('kpi_metrics', 'calculation_type')) {
                $table->string('calculation_type')->nullable()->after('unit');
            }

            if (! Schema::hasColumn('kpi_metrics', 'deleted_at')) {
                $table->softDeletes()->after('updated_by_id');
            }

            $table->index(['metric_group', 'is_active', 'sort_order'], 'kpi_metrics_group_active_sort_idx');
        });
    }

    private function extendKpiTargetsTable(): void
    {
        if (! Schema::hasTable('kpi_targets')) {
            return;
        }

        Schema::table('kpi_targets', function (Blueprint $table): void {
            if (! Schema::hasColumn('kpi_targets', 'uuid')) {
                $table->uuid('uuid')->nullable()->unique()->after('id');
            }

            if (! Schema::hasColumn('kpi_targets', 'user_id')) {
                $table->foreignId('user_id')
                    ->nullable()
                    ->after('branch_id')
                    ->constrained('users')
                    ->cascadeOnUpdate()
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('kpi_targets', 'period_type')) {
                $table->string('period_type')->default('month')->index()->after('user_id');
            }

            if (! Schema::hasColumn('kpi_targets', 'period_start')) {
                $table->date('period_start')->nullable()->index()->after('period_type');
            }

            if (! Schema::hasColumn('kpi_targets', 'period_end')) {
                $table->date('period_end')->nullable()->index()->after('period_start');
            }

            if (! Schema::hasColumn('kpi_targets', 'warning_threshold')) {
                $table->decimal('warning_threshold', 14, 4)->nullable()->after('target_value');
            }

            if (! Schema::hasColumn('kpi_targets', 'success_threshold')) {
                $table->decimal('success_threshold', 14, 4)->nullable()->after('warning_threshold');
            }

            if (! Schema::hasColumn('kpi_targets', 'deleted_at')) {
                $table->softDeletes()->after('updated_by_id');
            }

            $table->index(['kpi_metric_id', 'period_type', 'period_start'], 'kpi_targets_metric_period_type_start_idx');
            $table->index(['branch_id', 'user_id', 'period_type'], 'kpi_targets_branch_user_period_idx');
        });
    }

    private function extendKpiSnapshotsTable(): void
    {
        if (! Schema::hasTable('kpi_snapshots')) {
            return;
        }

        Schema::table('kpi_snapshots', function (Blueprint $table): void {
            if (! Schema::hasColumn('kpi_snapshots', 'uuid')) {
                $table->uuid('uuid')->nullable()->unique()->after('id');
            }

            if (! Schema::hasColumn('kpi_snapshots', 'user_id')) {
                $table->foreignId('user_id')
                    ->nullable()
                    ->after('branch_id')
                    ->constrained('users')
                    ->cascadeOnUpdate()
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('kpi_snapshots', 'period_type')) {
                $table->string('period_type')->default('day')->index()->after('user_id');
            }

            if (! Schema::hasColumn('kpi_snapshots', 'period_start')) {
                $table->date('period_start')->nullable()->index()->after('period_type');
            }

            if (! Schema::hasColumn('kpi_snapshots', 'period_end')) {
                $table->date('period_end')->nullable()->index()->after('period_start');
            }

            if (! Schema::hasColumn('kpi_snapshots', 'metadata')) {
                $table->json('metadata')->nullable()->after('calculated_at');
            }

            $table->index(['kpi_metric_id', 'period_type', 'period_start'], 'kpi_snapshots_metric_period_type_start_idx');
            $table->index(['branch_id', 'user_id', 'period_type'], 'kpi_snapshots_branch_user_period_idx');
        });
    }

    private function revertKpiMetricsTable(): void
    {
        if (! Schema::hasTable('kpi_metrics')) {
            return;
        }

        Schema::table('kpi_metrics', function (Blueprint $table): void {
            $table->dropIndex('kpi_metrics_group_active_sort_idx');

            if (Schema::hasColumn('kpi_metrics', 'uuid')) {
                $table->dropUnique('kpi_metrics_uuid_unique');
            }

            foreach (['uuid', 'metric_group', 'calculation_type', 'deleted_at'] as $column) {
                if (Schema::hasColumn('kpi_metrics', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    private function revertKpiTargetsTable(): void
    {
        if (! Schema::hasTable('kpi_targets')) {
            return;
        }

        Schema::table('kpi_targets', function (Blueprint $table): void {
            $table->dropIndex('kpi_targets_metric_period_type_start_idx');
            $table->dropIndex('kpi_targets_branch_user_period_idx');

            if (Schema::hasColumn('kpi_targets', 'uuid')) {
                $table->dropUnique('kpi_targets_uuid_unique');
            }

            if (Schema::hasColumn('kpi_targets', 'user_id')) {
                $table->dropConstrainedForeignId('user_id');
            }

            foreach ([
                'uuid',
                'period_type',
                'period_start',
                'period_end',
                'warning_threshold',
                'success_threshold',
                'deleted_at',
            ] as $column) {
                if (Schema::hasColumn('kpi_targets', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    private function revertKpiSnapshotsTable(): void
    {
        if (! Schema::hasTable('kpi_snapshots')) {
            return;
        }

        Schema::table('kpi_snapshots', function (Blueprint $table): void {
            $table->dropIndex('kpi_snapshots_metric_period_type_start_idx');
            $table->dropIndex('kpi_snapshots_branch_user_period_idx');

            if (Schema::hasColumn('kpi_snapshots', 'uuid')) {
                $table->dropUnique('kpi_snapshots_uuid_unique');
            }

            if (Schema::hasColumn('kpi_snapshots', 'user_id')) {
                $table->dropConstrainedForeignId('user_id');
            }

            foreach (['uuid', 'period_type', 'period_start', 'period_end', 'metadata'] as $column) {
                if (Schema::hasColumn('kpi_snapshots', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
