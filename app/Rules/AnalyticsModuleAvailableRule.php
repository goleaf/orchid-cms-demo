<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Schema;

class AnalyticsModuleAvailableRule implements ValidationRule
{
    /**
     * @param  array<string, string>  $moduleTables
     */
    public function __construct(private readonly array $moduleTables = []) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || $value === '') {
            $fail(tkey('analytics.validation.module_not_available'));

            return;
        }

        $table = $this->moduleTable($value);

        if ($table !== null && Schema::hasTable($table)) {
            return;
        }

        $fail(tkey('analytics.validation.module_not_available'));
    }

    private function moduleTable(string $module): ?string
    {
        $map = array_merge([
            'analytics_dashboards' => 'analytics_dashboards',
            'dashboard_widgets' => 'dashboard_widgets',
            'reports' => 'report_definitions',
            'report_definitions' => 'report_definitions',
            'report_runs' => 'report_runs',
            'report_exports' => 'report_exports',
            'kpis' => 'kpi_metrics',
            'kpi_metrics' => 'kpi_metrics',
            'kpi_targets' => 'kpi_targets',
            'kpi_snapshots' => 'kpi_snapshots',
            'analytics_snapshots' => 'analytics_snapshots',
            'analytics_cache' => 'analytics_cache',
            'analytics_cache_entries' => 'analytics_cache_entries',
            'crm' => 'marketing_leads',
            'crm_leads' => 'marketing_leads',
            'sales' => 'marketing_leads',
            'students' => 'student_profiles',
            'enrollments' => 'enrollments',
            'education' => 'training_groups',
            'groups' => 'training_groups',
            'schedule' => 'training_group_schedule_patterns',
            'lessons' => 'driving_lessons',
            'driving' => 'driving_lessons',
            'driving_lessons' => 'driving_lessons',
            'documents' => 'student_documents',
            'finance' => 'payments',
            'payments' => 'payments',
            'exams' => 'exam_sessions',
            'notifications' => 'notification_messages',
            'communications' => 'communication_messages',
            'staff' => 'users',
            'system' => 'users',
        ], $this->moduleTables);

        return $map[$module] ?? null;
    }
}
