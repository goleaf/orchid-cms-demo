<?php

declare(strict_types=1);

namespace App\Orchid\Screens\School;

use App\Actions\Analytics\GetOwnerDashboardAction;
use App\Models\DashboardWidget;
use App\Models\KpiSnapshot;
use App\Models\ReportDefinition;
use App\Models\ReportRun;
use Orchid\Screen\Action;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class AnalyticsDashboardScreen extends Screen
{
    /**
     * @return array<string, mixed>
     */
    public function query(GetOwnerDashboardAction $dashboard): iterable
    {
        $data = $dashboard->handle();
        $metrics = $data['metrics'];

        return [
            'open_leads' => number_format((int) $metrics['open_leads']),
            'converted_leads' => number_format((int) $metrics['converted_leads']),
            'active_students' => number_format((int) $metrics['active_students']),
            'active_enrollments' => number_format((int) $metrics['active_enrollments']),
            'lessons_today' => number_format((int) $metrics['lessons_today']),
            'scheduled_exams' => number_format((int) $metrics['scheduled_exams']),
            'paid_revenue' => number_format(((int) $metrics['paid_revenue_cents']) / 100, 2).' EUR',
            'pending_documents' => number_format((int) $metrics['pending_documents']),
            'queued_notifications' => number_format((int) $metrics['queued_notifications']),
            'analyticsDashboard' => $data['dashboard'],
            'dashboardWidgets' => $data['widgets'],
            'reportDefinitions' => $data['reportDefinitions'],
            'recentReportRuns' => $data['recentReportRuns'],
            'kpiSnapshots' => $data['kpiSnapshots'],
        ];
    }

    public function name(): ?string
    {
        return tkey('analytics.dashboard.title');
    }

    public function description(): ?string
    {
        return tkey('analytics.dashboard.description');
    }

    public function permission(): iterable
    {
        return ['analytics.dashboard.view'];
    }

    /**
     * @return Action[]
     */
    public function commandBar(): iterable
    {
        return [
            Link::make(tkey('menu.dashboard'))
                ->icon('bs.speedometer2')
                ->route('platform.main'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::metrics([
                tkey('analytics.metrics.open_leads') => 'open_leads',
                tkey('analytics.metrics.converted_leads') => 'converted_leads',
                tkey('analytics.metrics.active_students') => 'active_students',
                tkey('analytics.metrics.active_enrollments') => 'active_enrollments',
                tkey('analytics.metrics.lessons_today') => 'lessons_today',
                tkey('analytics.metrics.scheduled_exams') => 'scheduled_exams',
                tkey('analytics.metrics.paid_revenue') => 'paid_revenue',
                tkey('analytics.metrics.pending_documents') => 'pending_documents',
                tkey('analytics.metrics.queued_notifications') => 'queued_notifications',
            ]),

            Layout::table('dashboardWidgets', [
                TD::make('code', tkey('analytics.columns.code'))
                    ->render(fn (DashboardWidget $widget): string => $widget->code),
                TD::make('name', tkey('analytics.columns.name'))
                    ->render(fn (DashboardWidget $widget): string => $widget->displayName()),
                TD::make('widget_type', tkey('analytics.columns.type'))
                    ->render(fn (DashboardWidget $widget): string => $widget->widget_type),
                TD::make('metric_code', tkey('analytics.columns.metric'))
                    ->render(fn (DashboardWidget $widget): string => $widget->metric_code ?? data_get($widget->config, 'metric', '-')),
            ])->title(tkey('analytics.tables.widgets')),

            Layout::table('reportDefinitions', [
                TD::make('code', tkey('analytics.columns.code'))
                    ->render(fn (ReportDefinition $definition): string => $definition->code),
                TD::make('name', tkey('analytics.columns.name'))
                    ->render(fn (ReportDefinition $definition): string => $definition->displayName()),
                TD::make('report_type', tkey('analytics.columns.type'))
                    ->render(fn (ReportDefinition $definition): string => $definition->report_type->value),
                TD::make('runs_count', tkey('analytics.columns.runs'))
                    ->render(fn (ReportDefinition $definition): string => (string) $definition->runs_count),
                TD::make('exports_count', tkey('analytics.columns.exports'))
                    ->render(fn (ReportDefinition $definition): string => (string) $definition->exports_count),
            ])->title(tkey('analytics.tables.reports')),

            Layout::table('recentReportRuns', [
                TD::make('report', tkey('analytics.columns.report'))
                    ->render(fn (ReportRun $run): string => $run->definition?->displayName() ?? '-'),
                TD::make('status', tkey('analytics.columns.status'))
                    ->render(fn (ReportRun $run): string => $run->status->value),
                TD::make('row_count', tkey('analytics.columns.row_count'))
                    ->render(fn (ReportRun $run): string => (string) $run->row_count),
                TD::make('started_at', tkey('analytics.columns.started_at'))
                    ->render(fn (ReportRun $run): string => $run->started_at?->format('Y-m-d H:i') ?? '-'),
                TD::make('finished_at', tkey('analytics.columns.finished_at'))
                    ->render(fn (ReportRun $run): string => $run->finished_at?->format('Y-m-d H:i') ?? '-'),
            ])->title(tkey('analytics.tables.runs')),

            Layout::table('kpiSnapshots', [
                TD::make('metric', tkey('analytics.columns.metric'))
                    ->render(fn (KpiSnapshot $snapshot): string => $snapshot->metric?->displayName() ?? '-'),
                TD::make('period', tkey('analytics.columns.period'))
                    ->render(fn (KpiSnapshot $snapshot): string => $snapshot->period->value),
                TD::make('value', tkey('analytics.columns.value'))
                    ->render(fn (KpiSnapshot $snapshot): string => (string) $snapshot->value),
                TD::make('target_value', tkey('analytics.columns.target'))
                    ->render(fn (KpiSnapshot $snapshot): string => $snapshot->target_value !== null ? (string) $snapshot->target_value : '-'),
                TD::make('status', tkey('analytics.columns.status'))
                    ->render(fn (KpiSnapshot $snapshot): string => $snapshot->status->value),
                TD::make('snapshot_date', tkey('analytics.columns.snapshot_date'))
                    ->render(fn (KpiSnapshot $snapshot): string => $snapshot->snapshot_date->toDateString()),
            ])->title(tkey('analytics.tables.snapshots')),
        ];
    }
}
