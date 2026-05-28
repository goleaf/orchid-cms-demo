<?php

namespace Tests\Feature;

use App\Enums\AnalyticsDashboardAudience;
use App\Enums\DashboardWidgetType;
use App\Models\AnalyticsDashboard;
use App\Models\DashboardWidget;
use App\Models\User;
use App\Models\UserDashboardPreference;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AnalyticsDashboardDataModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_schema_matches_local_analytics_contract(): void
    {
        $columns = [
            'analytics_dashboards' => [
                'id',
                'uuid',
                'code',
                'name_translations',
                'description_translations',
                'audience',
                'is_active',
                'is_default',
                'sort_order',
                'created_by_id',
                'updated_by_id',
                'deleted_at',
                'created_at',
                'updated_at',
            ],
            'dashboard_widgets' => [
                'id',
                'uuid',
                'analytics_dashboard_id',
                'code',
                'widget_type',
                'title_translations',
                'description_translations',
                'config',
                'filters',
                'width',
                'height',
                'sort_order',
                'is_active',
                'created_by_id',
                'updated_by_id',
                'deleted_at',
                'created_at',
                'updated_at',
            ],
            'user_dashboard_preferences' => [
                'id',
                'user_id',
                'analytics_dashboard_id',
                'layout',
                'filters',
                'is_default',
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

    public function test_models_factories_relationships_scopes_and_translated_names_work(): void
    {
        $user = User::factory()->create();
        $dashboard = AnalyticsDashboard::factory()
            ->createdBy($user)
            ->default()
            ->audience(AnalyticsDashboardAudience::Owner)
            ->create([
                'code' => 'owner_overview',
                'name_translations' => ['en' => 'Owner overview', 'ru' => 'Панель владельца'],
                'description_translations' => ['en' => 'Daily school overview'],
            ]);
        $inactiveDashboard = AnalyticsDashboard::factory()->inactive()->create();

        $widget = DashboardWidget::factory()
            ->forDashboard($dashboard)
            ->createdBy($user)
            ->type(DashboardWidgetType::Chart)
            ->create([
                'code' => 'lead_chart',
                'title_translations' => ['en' => 'Lead chart', 'ru' => 'График лидов'],
                'description_translations' => ['en' => 'Lead movement'],
            ]);
        $preference = UserDashboardPreference::factory()
            ->forDashboard($dashboard)
            ->create(['user_id' => $user->id]);

        $this->assertNotEmpty($dashboard->uuid);
        $this->assertSame('Owner overview', $dashboard->displayName('en'));
        $this->assertSame('Daily school overview', $dashboard->displayDescription('en'));
        $this->assertTrue(AnalyticsDashboard::query()->active()->default()->whereKey($dashboard->id)->exists());
        $this->assertFalse(AnalyticsDashboard::query()->active()->whereKey($inactiveDashboard->id)->exists());
        $this->assertTrue($dashboard->widgets->first()->is($widget));

        $this->assertTrue($widget->dashboard->is($dashboard));
        $this->assertSame('Lead chart', $widget->displayTitle('en'));
        $this->assertSame('Lead chart', $widget->displayName('en'));
        $this->assertSame('Lead movement', $widget->displayDescription('en'));
        $this->assertTrue(DashboardWidget::query()->forDashboard($dashboard)->ofType(DashboardWidgetType::Chart)->active()->firstOrFail()->is($widget));

        $this->assertTrue($preference->analyticsDashboard->is($dashboard));
        $this->assertTrue($preference->user->is($user));
        $this->assertTrue(UserDashboardPreference::query()->forDashboard($dashboard)->default()->firstOrFail()->is($preference));
    }

    public function test_dashboard_audience_and_widget_type_values_are_local_role_values(): void
    {
        $this->assertSame([
            'owner',
            'director',
            'manager',
            'administrator',
            'instructor',
            'finance',
            'marketing',
            'system',
        ], AnalyticsDashboardAudience::values());

        $this->assertSame([
            'counter',
            'chart',
            'table',
            'funnel',
            'progress',
            'ranking',
            'alert',
            'calendar_summary',
        ], DashboardWidgetType::values());
    }
}
