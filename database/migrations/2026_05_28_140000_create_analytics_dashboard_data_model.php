<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->createAnalyticsDashboardsTable();
        $this->extendDashboardWidgetsTable();
        $this->extendUserDashboardPreferencesTable();
    }

    public function down(): void
    {
        $this->revertUserDashboardPreferencesTable();
        $this->revertDashboardWidgetsTable();

        Schema::dropIfExists('analytics_dashboards');
    }

    private function createAnalyticsDashboardsTable(): void
    {
        if (Schema::hasTable('analytics_dashboards')) {
            return;
        }

        Schema::create('analytics_dashboards', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('code')->unique();
            $table->json('name_translations')->nullable();
            $table->json('description_translations')->nullable();
            $table->string('audience')->index();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->foreignId('created_by_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('updated_by_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['audience', 'is_active', 'is_default'], 'analytics_dashboards_audience_default_idx');
            $table->index(['is_active', 'sort_order'], 'analytics_dashboards_active_sort_idx');
        });
    }

    private function extendDashboardWidgetsTable(): void
    {
        if (! Schema::hasTable('dashboard_widgets')) {
            return;
        }

        Schema::table('dashboard_widgets', function (Blueprint $table): void {
            if (! Schema::hasColumn('dashboard_widgets', 'uuid')) {
                $table->uuid('uuid')->nullable()->unique();
            }

            if (! Schema::hasColumn('dashboard_widgets', 'analytics_dashboard_id')) {
                $table->foreignId('analytics_dashboard_id')
                    ->nullable()
                    ->constrained('analytics_dashboards')
                    ->cascadeOnUpdate()
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('dashboard_widgets', 'title_translations')) {
                $table->json('title_translations')->nullable();
            }

            if (! Schema::hasColumn('dashboard_widgets', 'config')) {
                $table->json('config')->nullable();
            }

            if (! Schema::hasColumn('dashboard_widgets', 'filters')) {
                $table->json('filters')->nullable();
            }

            if (! Schema::hasColumn('dashboard_widgets', 'width')) {
                $table->unsignedTinyInteger('width')->default(3);
            }

            if (! Schema::hasColumn('dashboard_widgets', 'height')) {
                $table->unsignedTinyInteger('height')->default(1);
            }

            if (! Schema::hasColumn('dashboard_widgets', 'deleted_at')) {
                $table->softDeletes();
            }

            $table->index(['analytics_dashboard_id', 'is_active', 'sort_order'], 'dashboard_widgets_dashboard_active_sort_idx');
        });
    }

    private function extendUserDashboardPreferencesTable(): void
    {
        if (! Schema::hasTable('user_dashboard_preferences')) {
            return;
        }

        Schema::table('user_dashboard_preferences', function (Blueprint $table): void {
            if (! Schema::hasColumn('user_dashboard_preferences', 'analytics_dashboard_id')) {
                $table->foreignId('analytics_dashboard_id')
                    ->nullable()
                    ->constrained('analytics_dashboards')
                    ->cascadeOnUpdate()
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('user_dashboard_preferences', 'layout')) {
                $table->json('layout')->nullable();
            }

            $table->index(['user_id', 'analytics_dashboard_id'], 'user_dashboard_preferences_user_dashboard_model_idx');
        });
    }

    private function revertDashboardWidgetsTable(): void
    {
        if (! Schema::hasTable('dashboard_widgets')) {
            return;
        }

        Schema::table('dashboard_widgets', function (Blueprint $table): void {
            $table->dropIndex('dashboard_widgets_dashboard_active_sort_idx');

            if (Schema::hasColumn('dashboard_widgets', 'analytics_dashboard_id')) {
                $table->dropConstrainedForeignId('analytics_dashboard_id');
            }

            if (Schema::hasColumn('dashboard_widgets', 'uuid')) {
                $table->dropUnique('dashboard_widgets_uuid_unique');
                $table->dropColumn('uuid');
            }

            foreach (['title_translations', 'config', 'filters', 'width', 'height', 'deleted_at'] as $column) {
                if (Schema::hasColumn('dashboard_widgets', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    private function revertUserDashboardPreferencesTable(): void
    {
        if (! Schema::hasTable('user_dashboard_preferences')) {
            return;
        }

        Schema::table('user_dashboard_preferences', function (Blueprint $table): void {
            $table->dropIndex('user_dashboard_preferences_user_dashboard_model_idx');

            if (Schema::hasColumn('user_dashboard_preferences', 'analytics_dashboard_id')) {
                $table->dropConstrainedForeignId('analytics_dashboard_id');
            }

            if (Schema::hasColumn('user_dashboard_preferences', 'layout')) {
                $table->dropColumn('layout');
            }
        });
    }
};
