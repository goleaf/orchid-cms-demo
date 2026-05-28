<?php

namespace App\Http\Requests\Analytics\Concerns;

use App\Models\Branch;
use App\Models\TrainingGroup;
use App\Models\TrainingProgram;
use App\Models\User;
use App\Rules\AnalyticsDateRangeRule;
use App\Rules\ReportColumnAllowedRule;
use App\Rules\ReportFilterValueAllowedRule;
use App\Rules\ValidKpiPeriodRule;
use App\Rules\ValidReportFilterRule;
use Illuminate\Validation\Rule;

trait UsesAnalyticsRequestValidation
{
    protected function analyticsAccess(string $permission): bool
    {
        return $this->user()?->hasAccess($permission) ?? false;
    }

    /**
     * @param  array<string, string>  $messages
     * @return array<string, string>
     */
    protected function analyticsValidationMessages(array $messages = []): array
    {
        return array_replace([
            'dashboard.code.required' => tkey('analytics.validation.code_required'),
            'dashboard.name_translations.required' => tkey('analytics.validation.name_required'),
            'widget.code.required' => tkey('analytics.validation.code_required'),
            'widget.title_translations.required' => tkey('analytics.validation.name_required'),
            'report_definition_id.required' => tkey('analytics.validation.report_required'),
            'report_definition_id.exists' => tkey('analytics.validation.report_not_active'),
            'report_run_id.exists' => tkey('analytics.validation.export_not_allowed'),
            'format.required' => tkey('analytics.validation.invalid_format'),
            'metric.code.required' => tkey('analytics.validation.code_required'),
            'metric.name_translations.required' => tkey('analytics.validation.name_required'),
            'target.kpi_metric_id.required' => tkey('analytics.validation.metric_required'),
            'target.target_value.required' => tkey('analytics.validation.target_required'),
            'cache_key.required' => tkey('analytics.validation.invalid_cache_key'),
        ], $messages);
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return $this->analyticsValidationAttributes();
    }

    /**
     * @return array<string, string>
     */
    protected function analyticsValidationAttributes(): array
    {
        return [
            'dashboard.id' => tkey('validation.attributes.analytics.dashboard'),
            'dashboard.code' => tkey('validation.attributes.analytics.dashboard_code'),
            'dashboard.name_translations' => tkey('validation.attributes.analytics.dashboard_name'),
            'dashboard.description_translations' => tkey('validation.attributes.analytics.dashboard_description'),
            'dashboard.audience' => tkey('validation.attributes.analytics.dashboard_audience'),
            'dashboard.is_active' => tkey('validation.attributes.analytics.is_active'),
            'dashboard.is_default' => tkey('validation.attributes.analytics.is_default'),
            'dashboard.sort_order' => tkey('validation.attributes.analytics.sort_order'),
            'widget.analytics_dashboard_id' => tkey('validation.attributes.analytics.dashboard'),
            'widget.code' => tkey('validation.attributes.analytics.widget_code'),
            'widget.widget_type' => tkey('validation.attributes.analytics.widget_type'),
            'widget.title_translations' => tkey('validation.attributes.analytics.widget_title'),
            'widget.description_translations' => tkey('validation.attributes.analytics.widget_description'),
            'widget.config' => tkey('validation.attributes.analytics.widget_config'),
            'widget.filters' => tkey('validation.attributes.analytics.filters'),
            'widget.width' => tkey('validation.attributes.analytics.width'),
            'widget.height' => tkey('validation.attributes.analytics.height'),
            'widget.sort_order' => tkey('validation.attributes.analytics.sort_order'),
            'report.id' => tkey('validation.attributes.analytics.report'),
            'report.code' => tkey('validation.attributes.analytics.report_code'),
            'report.name_translations' => tkey('validation.attributes.analytics.report_name'),
            'report.description_translations' => tkey('validation.attributes.analytics.report_description'),
            'report.report_group' => tkey('validation.attributes.analytics.report_group'),
            'report.data_source' => tkey('validation.attributes.analytics.data_source'),
            'report.filters_schema' => tkey('validation.attributes.analytics.filters'),
            'report.columns_schema' => tkey('validation.attributes.analytics.columns'),
            'report.permissions' => tkey('validation.attributes.analytics.permissions'),
            'report.report_type' => tkey('validation.attributes.analytics.report_type'),
            'report.source_model' => tkey('validation.attributes.analytics.data_source'),
            'report.default_filters' => tkey('validation.attributes.analytics.filters'),
            'report.column_config' => tkey('validation.attributes.analytics.columns'),
            'report.schedule' => tkey('validation.attributes.analytics.schedule'),
            'report_definition_id' => tkey('validation.attributes.analytics.report'),
            'report_run_id' => tkey('validation.attributes.analytics.report_run'),
            'format' => tkey('validation.attributes.analytics.export_format'),
            'filters' => tkey('validation.attributes.analytics.filters'),
            'filters.period_type' => tkey('validation.attributes.analytics.period_type'),
            'filters.period_start' => tkey('validation.attributes.analytics.period_start'),
            'filters.period_end' => tkey('validation.attributes.analytics.period_end'),
            'filters.branch_id' => tkey('validation.attributes.analytics.branch'),
            'filters.user_id' => tkey('validation.attributes.analytics.user'),
            'filters.training_program_id' => tkey('validation.attributes.analytics.training_program'),
            'filters.training_group_id' => tkey('validation.attributes.analytics.training_group'),
            'filters.instructor_id' => tkey('validation.attributes.analytics.instructor'),
            'filters.manager_id' => tkey('validation.attributes.analytics.manager'),
            'filters.status' => tkey('validation.attributes.analytics.status'),
            'filters.source' => tkey('validation.attributes.analytics.source'),
            'filters.columns' => tkey('validation.attributes.analytics.columns'),
            'columns' => tkey('validation.attributes.analytics.columns'),
            'metric.code' => tkey('validation.attributes.analytics.kpi_code'),
            'metric.name_translations' => tkey('validation.attributes.analytics.kpi_name'),
            'metric.description_translations' => tkey('validation.attributes.analytics.kpi_description'),
            'metric.metric_group' => tkey('validation.attributes.analytics.kpi_group'),
            'metric.unit' => tkey('validation.attributes.analytics.kpi_unit'),
            'metric.calculation_type' => tkey('validation.attributes.analytics.calculation_type'),
            'target.kpi_metric_id' => tkey('validation.attributes.analytics.kpi_metric'),
            'target.period_type' => tkey('validation.attributes.analytics.period_type'),
            'target.period_start' => tkey('validation.attributes.analytics.period_start'),
            'target.period_end' => tkey('validation.attributes.analytics.period_end'),
            'target.branch_id' => tkey('validation.attributes.analytics.branch'),
            'target.user_id' => tkey('validation.attributes.analytics.user'),
            'target.target_value' => tkey('validation.attributes.analytics.target_value'),
            'target.warning_threshold' => tkey('validation.attributes.analytics.warning_threshold'),
            'target.success_threshold' => tkey('validation.attributes.analytics.success_threshold'),
            'target.warning_value' => tkey('validation.attributes.analytics.warning_threshold'),
            'target.assigned_to_user_id' => tkey('validation.attributes.analytics.user'),
            'cache_key' => tkey('validation.attributes.analytics.cache_key'),
            'data' => tkey('validation.attributes.analytics.cache_data'),
            'tags' => tkey('validation.attributes.analytics.cache_tags'),
            'ttl_seconds' => tkey('validation.attributes.analytics.cache_ttl'),
            'ttl_minutes' => tkey('validation.attributes.analytics.cache_ttl'),
            'expires_at' => tkey('validation.attributes.analytics.expires_at'),
            'module' => tkey('validation.attributes.analytics.module'),
            'period_type' => tkey('validation.attributes.analytics.period_type'),
            'period_start' => tkey('validation.attributes.analytics.period_start'),
            'period_end' => tkey('validation.attributes.analytics.period_end'),
            'branch_id' => tkey('validation.attributes.analytics.branch'),
            'user_id' => tkey('validation.attributes.analytics.user'),
            'preferences.analytics_dashboard_id' => tkey('validation.attributes.analytics.dashboard'),
            'preferences.layout' => tkey('validation.attributes.analytics.layout'),
            'preferences.filters' => tkey('validation.attributes.analytics.filters'),
            'preferences.refresh_interval_seconds' => tkey('validation.attributes.analytics.refresh_interval'),
            'preferences.timezone' => tkey('validation.attributes.analytics.timezone'),
        ];
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    protected function analyticsFilterRules(string $prefix = 'filters'): array
    {
        return [
            $prefix => ['nullable', 'array', new ValidReportFilterRule],
            "{$prefix}.period_type" => ['nullable', 'string', new ValidKpiPeriodRule],
            "{$prefix}.period_start" => ['nullable', 'date', new AnalyticsDateRangeRule("{$prefix}.period_start", "{$prefix}.period_end")],
            "{$prefix}.period_end" => ['nullable', 'date'],
            "{$prefix}.start_date" => ['nullable', 'date', new AnalyticsDateRangeRule("{$prefix}.start_date", "{$prefix}.end_date")],
            "{$prefix}.end_date" => ['nullable', 'date'],
            "{$prefix}.branch_id" => ['nullable', 'integer', Rule::exists(Branch::class, 'id')],
            "{$prefix}.user_id" => ['nullable', 'integer', Rule::exists(User::class, 'id')],
            "{$prefix}.training_program_id" => ['nullable', 'integer', Rule::exists(TrainingProgram::class, 'id')],
            "{$prefix}.training_group_id" => ['nullable', 'integer', Rule::exists(TrainingGroup::class, 'id')],
            "{$prefix}.instructor_id" => ['nullable', 'integer', Rule::exists(User::class, 'id')],
            "{$prefix}.manager_id" => ['nullable', 'integer', Rule::exists(User::class, 'id')],
            "{$prefix}.status" => ['nullable', new ReportFilterValueAllowedRule],
            "{$prefix}.source" => ['nullable', new ReportFilterValueAllowedRule],
            "{$prefix}.report_group" => ['nullable', new ReportFilterValueAllowedRule],
            "{$prefix}.columns" => ['nullable', new ReportColumnAllowedRule],
        ];
    }
}
