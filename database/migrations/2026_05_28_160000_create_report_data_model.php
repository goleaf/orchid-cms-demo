<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->extendReportDefinitionsTable();
        $this->extendReportRunsTable();
        $this->extendReportExportsTable();
    }

    public function down(): void
    {
        $this->revertReportExportsTable();
        $this->revertReportRunsTable();
        $this->revertReportDefinitionsTable();
    }

    private function extendReportDefinitionsTable(): void
    {
        if (! Schema::hasTable('report_definitions')) {
            return;
        }

        Schema::table('report_definitions', function (Blueprint $table): void {
            if (! Schema::hasColumn('report_definitions', 'uuid')) {
                $table->uuid('uuid')->nullable()->unique()->after('id');
            }

            if (! Schema::hasColumn('report_definitions', 'report_group')) {
                $table->string('report_group')->default('system')->index()->after('description_translations');
            }

            if (! Schema::hasColumn('report_definitions', 'data_source')) {
                $table->string('data_source')->nullable()->after('report_group');
            }

            if (! Schema::hasColumn('report_definitions', 'filters_schema')) {
                $table->json('filters_schema')->nullable()->after('data_source');
            }

            if (! Schema::hasColumn('report_definitions', 'columns_schema')) {
                $table->json('columns_schema')->nullable()->after('filters_schema');
            }

            if (! Schema::hasColumn('report_definitions', 'permissions')) {
                $table->json('permissions')->nullable()->after('columns_schema');
            }

            if (! Schema::hasColumn('report_definitions', 'deleted_at')) {
                $table->softDeletes()->after('updated_by_id');
            }

            $table->index(['report_group', 'is_active', 'sort_order'], 'report_definitions_group_active_sort_idx');
        });
    }

    private function extendReportRunsTable(): void
    {
        if (! Schema::hasTable('report_runs')) {
            return;
        }

        Schema::table('report_runs', function (Blueprint $table): void {
            if (! Schema::hasColumn('report_runs', 'user_id')) {
                $table->foreignId('user_id')
                    ->nullable()
                    ->after('report_definition_id')
                    ->constrained('users')
                    ->cascadeOnUpdate()
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('report_runs', 'metadata')) {
                $table->json('metadata')->nullable()->after('error_message');
            }

            $table->index(['user_id', 'status'], 'report_runs_user_status_idx');
        });
    }

    private function extendReportExportsTable(): void
    {
        if (! Schema::hasTable('report_exports')) {
            return;
        }

        Schema::table('report_exports', function (Blueprint $table): void {
            if (! Schema::hasColumn('report_exports', 'filename')) {
                $table->string('filename')->nullable()->after('path');
            }

            if (! Schema::hasColumn('report_exports', 'mime_type')) {
                $table->string('mime_type')->nullable()->after('filename');
            }

            if (! Schema::hasColumn('report_exports', 'size_bytes')) {
                $table->unsignedBigInteger('size_bytes')->nullable()->after('mime_type');
            }

            if (! Schema::hasColumn('report_exports', 'exported_by_id')) {
                $table->foreignId('exported_by_id')
                    ->nullable()
                    ->after('created_by_id')
                    ->constrained('users')
                    ->cascadeOnUpdate()
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('report_exports', 'metadata')) {
                $table->json('metadata')->nullable()->after('exported_by_id');
            }

            $table->index(['report_run_id', 'format'], 'report_exports_run_format_idx');
            $table->index(['exported_by_id', 'exported_at'], 'report_exports_exported_by_date_idx');
        });
    }

    private function revertReportDefinitionsTable(): void
    {
        if (! Schema::hasTable('report_definitions')) {
            return;
        }

        Schema::table('report_definitions', function (Blueprint $table): void {
            $table->dropIndex('report_definitions_group_active_sort_idx');

            if (Schema::hasColumn('report_definitions', 'uuid')) {
                $table->dropUnique('report_definitions_uuid_unique');
            }

            foreach ([
                'uuid',
                'report_group',
                'data_source',
                'filters_schema',
                'columns_schema',
                'permissions',
                'deleted_at',
            ] as $column) {
                if (Schema::hasColumn('report_definitions', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    private function revertReportRunsTable(): void
    {
        if (! Schema::hasTable('report_runs')) {
            return;
        }

        Schema::table('report_runs', function (Blueprint $table): void {
            $table->dropIndex('report_runs_user_status_idx');

            if (Schema::hasColumn('report_runs', 'user_id')) {
                $table->dropConstrainedForeignId('user_id');
            }

            if (Schema::hasColumn('report_runs', 'metadata')) {
                $table->dropColumn('metadata');
            }
        });
    }

    private function revertReportExportsTable(): void
    {
        if (! Schema::hasTable('report_exports')) {
            return;
        }

        Schema::table('report_exports', function (Blueprint $table): void {
            $table->dropIndex('report_exports_run_format_idx');
            $table->dropIndex('report_exports_exported_by_date_idx');

            if (Schema::hasColumn('report_exports', 'exported_by_id')) {
                $table->dropConstrainedForeignId('exported_by_id');
            }

            foreach (['filename', 'mime_type', 'size_bytes', 'metadata'] as $column) {
                if (Schema::hasColumn('report_exports', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
