<?php

namespace App\Actions;

use App\Enums\LeadStatus;
use App\Models\Branch;
use App\Models\LeadLostReason;
use App\Models\LeadSource;
use App\Models\MarketingLead;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class GetLeadPipelineAction
{
    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function handle(array $filters): array
    {
        $baseQuery = MarketingLead::query()
            ->forLeadList()
            ->with([
                'responsibleManager:id,name',
                'branch:id,name,city',
                'trainingProgram:id,title,license_category',
            ])
            ->withCount(['comments', 'communications', 'documents', 'openTasks', 'overdueTasks'])
            ->when(filled($filters['manager_id'] ?? null), fn (Builder $query) => $query->where('responsible_manager_id', $filters['manager_id']))
            ->when(filled($filters['source'] ?? null), fn (Builder $query) => $query->where('source', $filters['source']))
            ->when(filled($filters['license_category'] ?? null), fn (Builder $query) => $query->where('license_category', $filters['license_category']))
            ->when(filled($filters['branch_id'] ?? null), fn (Builder $query) => $query->where('branch_id', $filters['branch_id']))
            ->when(($filters['hot'] ?? null) === '1', fn (Builder $query) => $query->where('is_hot', true))
            ->when(($filters['overdue'] ?? null) === '1', fn (Builder $query) => $query
                ->where(function (Builder $inner): void {
                    $inner
                        ->where('next_follow_up_at', '<', now())
                        ->orWhereHas('overdueTasks');
                }));

        $leads = $baseQuery
            ->orderByDesc('is_hot')
            ->orderBy('next_follow_up_at')
            ->orderByDesc('created_at')
            ->limit(240)
            ->get();

        $statuses = collect(LeadStatus::cases());
        $sourceLabels = LeadSource::translatedLabels(
            $leads
                ->pluck('source')
                ->filter()
                ->unique()
                ->values()
                ->all(),
        );
        $lostReasonLabels = LeadLostReason::translatedLabels(
            $leads
                ->pluck('lost_reason_code')
                ->filter()
                ->unique()
                ->values()
                ->all(),
        );

        return [
            'statuses' => $statuses,
            'statusLabels' => $statuses
                ->mapWithKeys(fn (LeadStatus $status): array => [$status->value => $status->label()])
                ->all(),
            'columns' => $statuses->mapWithKeys(fn (LeadStatus $status): array => [
                $status->value => $leads
                    ->filter(fn (MarketingLead $lead): bool => $lead->status === $status)
                    ->values(),
            ])->all(),
            'filters' => [
                'manager_id' => $filters['manager_id'] ?? null,
                'source' => $filters['source'] ?? null,
                'license_category' => $filters['license_category'] ?? null,
                'branch_id' => $filters['branch_id'] ?? null,
                'hot' => $filters['hot'] ?? null,
                'overdue' => $filters['overdue'] ?? null,
            ],
            'filterOptions' => [
                'managers' => User::query()
                    ->select(['id', 'name'])
                    ->orderBy('name')
                    ->limit(100)
                    ->pluck('name', 'id')
                    ->all(),
                'sources' => LeadSource::translatedLabels(),
                'categories' => $this->distinctValues('license_category'),
                'branches' => Branch::query()
                    ->forAdminList()
                    ->orderBy('city')
                    ->pluck('name', 'id')
                    ->all(),
            ],
            'sourceLabels' => $sourceLabels,
            'lostReasonLabels' => $lostReasonLabels,
            'labels' => $this->labels(),
            'report' => $this->conversionReport($leads, $statuses),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function distinctValues(string $column): array
    {
        return MarketingLead::query()
            ->select([$column])
            ->whereNotNull($column)
            ->orderBy($column)
            ->limit(50)
            ->get()
            ->pluck($column, $column)
            ->filter()
            ->all();
    }

    /**
     * @param  Collection<int, MarketingLead>  $leads
     * @param  Collection<int, LeadStatus>  $statuses
     * @return array<string, mixed>
     */
    private function conversionReport(Collection $leads, Collection $statuses): array
    {
        $total = max(1, $leads->count());
        $becameStudents = $leads
            ->filter(fn (MarketingLead $lead): bool => $lead->status === LeadStatus::BecameStudent)
            ->count();
        $rejected = $leads
            ->filter(fn (MarketingLead $lead): bool => $lead->status === LeadStatus::Rejected)
            ->count();

        return [
            'total' => $leads->count(),
            'became_students' => $becameStudents,
            'rejected' => $rejected,
            'conversion_rate' => round(($becameStudents / $total) * 100, 1),
            'loss_rate' => round(($rejected / $total) * 100, 1),
            'loss_reasons' => $leads
                ->filter(fn (MarketingLead $lead): bool => $lead->status === LeadStatus::Rejected)
                ->pluck('lost_reason_code')
                ->filter()
                ->countBy()
                ->sortDesc()
                ->take(5)
                ->all(),
            'by_status' => $statuses->map(fn (LeadStatus $status): array => [
                'status' => $status,
                'count' => $leads
                    ->filter(fn (MarketingLead $lead): bool => $lead->status === $status)
                    ->count(),
            ])->all(),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function labels(): array
    {
        return [
            'manager' => tkey('crm.leads.fields.manager'),
            'all_managers' => tkey('crm.leads.filters.all_managers'),
            'no_managers_found' => tkey('crm.leads.empty.no_managers_found'),
            'source' => tkey('crm.leads.fields.source'),
            'all_sources' => tkey('crm.leads.filters.all_sources'),
            'no_sources_found' => tkey('crm.leads.empty.no_sources_found'),
            'category' => tkey('crm.leads.columns.category'),
            'all_categories' => tkey('crm.leads.filters.all_categories'),
            'no_categories_found' => tkey('crm.leads.empty.no_categories_found'),
            'branch' => tkey('crm.leads.fields.branch'),
            'all_branches' => tkey('crm.leads.filters.all_branches'),
            'no_branches_found' => tkey('crm.leads.empty.no_branches_found'),
            'flags' => tkey('crm.leads.filters.flags'),
            'hot' => tkey('crm.leads.flags.hot'),
            'overdue' => tkey('crm.leads.flags.overdue'),
            'filter' => tkey('common.actions.search'),
            'reset' => tkey('common.actions.reset'),
            'leads_in_view' => tkey('crm.pipeline.report.leads_in_view'),
            'became_students' => tkey('crm.pipeline.report.became_students'),
            'conversion' => tkey('crm.pipeline.report.conversion'),
            'loss_rate' => tkey('crm.pipeline.report.loss_rate'),
            'status_report' => tkey('crm.pipeline.report.status_conversion'),
            'loss_reasons' => tkey('crm.pipeline.report.loss_reasons'),
            'no_statuses_in_filter' => tkey('crm.pipeline.empty.no_statuses_in_filter'),
            'no_loss_reasons' => tkey('crm.pipeline.empty.no_loss_reasons'),
            'no_contact' => tkey('crm.leads.empty.no_contact'),
            'no_course' => tkey('crm.leads.empty.no_course'),
            'no_branch' => tkey('crm.leads.empty.no_branch'),
            'no_manager' => tkey('crm.leads.empty.no_manager'),
            'no_follow_up' => tkey('crm.leads.empty.no_follow_up'),
            'task_summary' => tkey('crm.tasks.summary'),
            'no_leads' => tkey('crm.leads.empty.no_leads'),
            'no_statuses' => tkey('crm.pipeline.empty.no_statuses'),
            'no_pipeline_statuses' => tkey('crm.pipeline.empty.no_pipeline_statuses'),
        ];
    }
}
