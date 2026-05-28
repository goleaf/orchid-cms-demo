<?php

namespace App\Actions;

use App\Enums\LeadStatus;
use App\Models\Branch;
use App\Models\CourseCategory;
use App\Models\LeadLostReason;
use App\Models\LeadSource;
use App\Models\LeadStatus as LeadStatusDictionary;
use App\Models\MarketingLead;
use App\Models\TrainingProgram;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class GetLeadPipelineAction
{
    private const DEFAULT_LIMIT_PER_COLUMN = 20;

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function handle(array $filters): array
    {
        $limitPerColumn = max(1, min(100, (int) ($filters['limit_per_column'] ?? self::DEFAULT_LIMIT_PER_COLUMN)));
        $statuses = $this->activeStatuses();
        $baseQuery = MarketingLead::query()
            ->forLeadList()
            ->with([
                'responsibleManager:id,name',
                'branch:id,name,name_translations,city,city_translations',
                'trainingProgram:id,title,title_translations,license_category',
                'trainingGroup:id,name,name_translations,code',
                'tags:id,slug,name,name_translations',
            ])
            ->withCount(['comments', 'communications', 'documents', 'openTasks', 'overdueTasks']);

        $baseQuery = app(FilterLeadsAction::class)->handle(
            $baseQuery,
            [
                ...$filters,
                'course_id' => $filters['course_id'] ?? $filters['training_program_id'] ?? null,
                'only_overdue' => $filters['only_overdue'] ?? $filters['overdue'] ?? null,
            ],
            filled($filters['current_user_id'] ?? null) ? User::query()->find((int) $filters['current_user_id']) : null,
            true,
        );

        $columns = $statuses->mapWithKeys(function (LeadStatusDictionary $status) use ($baseQuery, $limitPerColumn): array {
            $leads = (clone $baseQuery)
                ->where('status', $status->code)
                ->orderByDesc('is_hot')
                ->orderByDesc('lead_score')
                ->orderBy('next_follow_up_at')
                ->orderByDesc('created_at')
                ->limit($limitPerColumn)
                ->get();

            return [$status->code => $leads];
        });

        $leads = $columns->flatten(1);
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
                ->mapWithKeys(fn (LeadStatusDictionary $status): array => [$status->code => $status->displayName()])
                ->all(),
            'columns' => $columns->all(),
            'columnLimit' => $limitPerColumn,
            'filters' => [
                'manager_id' => $filters['manager_id'] ?? null,
                'source' => $filters['source'] ?? null,
                'training_program_id' => $filters['training_program_id'] ?? $filters['course_id'] ?? null,
                'course_category_id' => $filters['course_category_id'] ?? null,
                'license_category' => $filters['license_category'] ?? null,
                'branch_id' => $filters['branch_id'] ?? null,
                'only_my' => $filters['only_my'] ?? null,
                'hot' => $filters['hot'] ?? null,
                'overdue' => $filters['overdue'] ?? $filters['only_overdue'] ?? null,
                'created_from' => $filters['created_from'] ?? null,
                'created_to' => $filters['created_to'] ?? null,
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
                'programs' => TrainingProgram::query()
                    ->forAcademyList()
                    ->orderBy('sort_order')
                    ->orderBy('title')
                    ->limit(100)
                    ->get()
                    ->mapWithKeys(fn (TrainingProgram $program): array => [$program->id => $program->displayTitle()])
                    ->all(),
                'courseCategories' => CourseCategory::query()
                    ->active()
                    ->ordered()
                    ->limit(100)
                    ->get(['id', 'code', 'slug', 'name_translations'])
                    ->mapWithKeys(fn (CourseCategory $category): array => [$category->id => $category->displayName()])
                    ->all(),
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
     * @return Collection<int, LeadStatusDictionary>
     */
    private function activeStatuses(): Collection
    {
        $enumCodes = collect(LeadStatus::cases())
            ->map(fn (LeadStatus $status): string => $status->value)
            ->all();

        $statuses = LeadStatusDictionary::query()
            ->active()
            ->ordered()
            ->whereIn('code', $enumCodes)
            ->get(['id', 'code', 'name', 'name_translations', 'sort_order']);

        if ($statuses->isNotEmpty()) {
            return $statuses;
        }

        return collect(LeadStatus::cases())
            ->map(fn (LeadStatus $status): LeadStatusDictionary => new LeadStatusDictionary([
                'code' => $status->value,
                'name' => $status->label(),
                'name_translations' => null,
                'sort_order' => 0,
            ]));
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
     * @param  Collection<int, LeadStatusDictionary>  $statuses
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
            'by_status' => $statuses->map(fn (LeadStatusDictionary $status): array => [
                'status' => $status->code,
                'count' => $leads
                    ->filter(fn (MarketingLead $lead): bool => $lead->status->value === $status->code)
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
            'course' => tkey('crm.leads.fields.course'),
            'all_courses' => tkey('crm.leads.filters.all_courses'),
            'no_courses_found' => tkey('crm.leads.empty.no_course'),
            'category' => tkey('crm.leads.columns.category'),
            'all_categories' => tkey('crm.leads.filters.all_categories'),
            'no_categories_found' => tkey('crm.leads.empty.no_categories_found'),
            'course_category' => tkey('crm.leads.filters.course_category'),
            'branch' => tkey('crm.leads.fields.branch'),
            'all_branches' => tkey('crm.leads.filters.all_branches'),
            'no_branches_found' => tkey('crm.leads.empty.no_branches_found'),
            'flags' => tkey('crm.leads.filters.flags'),
            'only_my' => tkey('crm.leads.filters.only_my'),
            'created_from' => tkey('crm.leads.filters.created_from'),
            'created_to' => tkey('crm.leads.filters.created_to'),
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
            'lead_number' => tkey('crm.leads.fields.lead_number'),
            'created_at' => tkey('crm.leads.fields.created_at'),
            'next_follow_up' => tkey('crm.leads.fields.next_follow_up_at'),
            'duplicate' => tkey('crm.leads.statuses.duplicate'),
            'priority' => tkey('crm.leads.fields.priority'),
            'open_lead' => tkey('crm.pipeline.actions.open_lead'),
            'change_status' => tkey('crm.pipeline.actions.change_status'),
            'add_note' => tkey('crm.leads.actions.add_note'),
            'log_call' => tkey('crm.leads.actions.log_call'),
            'create_task' => tkey('crm.leads.actions.create_task'),
        ];
    }
}
