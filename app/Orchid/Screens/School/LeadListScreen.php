<?php

declare(strict_types=1);

namespace App\Orchid\Screens\School;

use App\Actions\ExportLeadsCsvAction;
use App\Actions\FilterLeadsAction;
use App\Enums\LeadStatus;
use App\Http\Requests\Marketing\ExportLeadsRequest;
use App\Models\Branch;
use App\Models\CourseCategory;
use App\Models\LeadLostReason;
use App\Models\LeadSource;
use App\Models\LeadStatus as LeadStatusDictionary;
use App\Models\LeadTag;
use App\Models\MarketingLead;
use App\Models\TrainingGroup;
use App\Models\TrainingProgram;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LeadListScreen extends Screen
{
    /**
     * @var array<string, string>
     */
    private array $sourceLabels = [];

    /**
     * @var array<string, mixed>
     */
    private array $filters = [];

    /**
     * @var array<int, string>
     */
    private array $managers = [];

    /**
     * @var array<int, string>
     */
    private array $branches = [];

    /**
     * @var array<int, string>
     */
    private array $programs = [];

    /**
     * @var array<int, string>
     */
    private array $categories = [];

    /**
     * @var array<int, string>
     */
    private array $groups = [];

    /**
     * @var array<int, string>
     */
    private array $tags = [];

    /**
     * @var array<string, string>
     */
    private array $lostReasons = [];

    public function query(Request $request): iterable
    {
        $this->filters = $this->filtersFromRequest($request);

        $this->managers = User::query()
            ->select(['id', 'name'])
            ->orderBy('name')
            ->limit(100)
            ->pluck('name', 'id')
            ->all();
        $this->branches = Branch::query()
            ->forAdminList()
            ->orderBy('sort_order')
            ->orderBy('city')
            ->get()
            ->mapWithKeys(fn (Branch $branch): array => [$branch->id => $branch->displayName()])
            ->all();
        $this->programs = TrainingProgram::query()
            ->forAcademyList()
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get()
            ->mapWithKeys(fn (TrainingProgram $program): array => [$program->id => $program->displayTitle()])
            ->all();
        $this->categories = CourseCategory::query()
            ->active()
            ->ordered()
            ->get(['id', 'code', 'slug', 'name_translations'])
            ->mapWithKeys(fn (CourseCategory $category): array => [$category->id => $category->displayName()])
            ->all();
        $this->groups = TrainingGroup::query()
            ->operationalList()
            ->orderBy('starts_on')
            ->get()
            ->mapWithKeys(fn (TrainingGroup $group): array => [$group->id => $group->displayName()])
            ->all();
        $this->tags = LeadTag::query()
            ->active()
            ->ordered()
            ->get(['id', 'slug', 'name', 'name_translations'])
            ->mapWithKeys(fn (LeadTag $tag): array => [$tag->id => $tag->displayName()])
            ->all();
        $this->lostReasons = LeadLostReason::translatedLabels();

        $leads = $this->leadQuery($request)
            ->orderByDesc('created_at')
            ->simplePaginate(15)
            ->withQueryString();

        $this->sourceLabels = LeadSource::translatedLabels(
            $leads->getCollection()
                ->pluck('source')
                ->filter()
                ->unique()
                ->values()
                ->all(),
        );

        return [
            'leads' => $leads,
        ];
    }

    public function name(): ?string
    {
        return tkey('crm.leads.title');
    }

    public function description(): ?string
    {
        return tkey('crm.leads.description');
    }

    public function permission(): iterable
    {
        return ['crm.leads.view', 'platform.marketing.leads'];
    }

    public function commandBar(): iterable
    {
        return [
            Link::make(tkey('crm.leads.actions.create'))
                ->icon('bs.plus-circle')
                ->route($this->leadCreateRoute()),

            Link::make(tkey('crm.leads.actions.open_pipeline'))
                ->icon('bs.kanban')
                ->route($this->pipelineRoute()),

            Link::make(tkey('menu.crm.tasks'))
                ->icon('bs.check2-square')
                ->route('platform.crm.tasks'),

            Button::make(tkey('common.actions.export_csv'))
                ->icon('bs.download')
                ->method('export')
                ->canSee(request()->user()?->hasAccess('crm.leads.export') ?? false),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::rows([
                Input::make('search')
                    ->title(tkey('crm.leads.filters.search'))
                    ->value($this->filters['search'] ?? ''),

                Select::make('status')
                    ->title(tkey('crm.leads.filters.status'))
                    ->empty(tkey('crm.leads.filters.all_statuses'), '')
                    ->options($this->leadStatusOptions())
                    ->value($this->filters['status'] ?? ''),

                Select::make('source')
                    ->title(tkey('crm.leads.filters.source'))
                    ->empty(tkey('crm.leads.filters.all_sources'), '')
                    ->options(LeadSource::translatedLabels())
                    ->value($this->filters['source'] ?? ''),

                Select::make('manager_id')
                    ->title(tkey('crm.leads.filters.manager'))
                    ->empty(tkey('crm.leads.filters.all_managers'), '')
                    ->options($this->managers)
                    ->value($this->filters['manager_id'] ?? ''),

                Select::make('branch_id')
                    ->title(tkey('crm.leads.filters.branch'))
                    ->empty(tkey('crm.leads.filters.all_branches'), '')
                    ->options($this->branches)
                    ->value($this->filters['branch_id'] ?? ''),

                Select::make('training_program_id')
                    ->title(tkey('crm.leads.filters.course'))
                    ->empty(tkey('crm.leads.filters.all_courses'), '')
                    ->options($this->programs)
                    ->value($this->filters['training_program_id'] ?? ''),

                Select::make('course_category_id')
                    ->title(tkey('crm.leads.filters.course_category'))
                    ->empty(tkey('crm.leads.filters.all_categories'), '')
                    ->options($this->categories)
                    ->value($this->filters['course_category_id'] ?? ''),

                Select::make('training_group_id')
                    ->title(tkey('crm.leads.filters.training_group'))
                    ->empty(tkey('crm.leads.filters.all_groups'), '')
                    ->options($this->groups)
                    ->value($this->filters['training_group_id'] ?? ''),

                Select::make('tag_id')
                    ->title(tkey('crm.leads.filters.tag'))
                    ->empty(tkey('crm.leads.filters.all_tags'), '')
                    ->options($this->tags)
                    ->value($this->filters['tag_id'] ?? ''),

                Select::make('lost_reason_code')
                    ->title(tkey('crm.leads.filters.lost_reason'))
                    ->empty(tkey('crm.leads.filters.all_lost_reasons'), '')
                    ->options($this->lostReasons)
                    ->value($this->filters['lost_reason_code'] ?? ''),

                Select::make('priority')
                    ->title(tkey('crm.leads.filters.priority'))
                    ->empty(tkey('crm.leads.filters.all_priorities'), '')
                    ->options($this->priorityOptions())
                    ->value($this->filters['priority'] ?? ''),

                Select::make('segment')
                    ->title(tkey('crm.leads.filters.segment'))
                    ->empty(tkey('crm.leads.filters.no_segment'), '')
                    ->options($this->segments())
                    ->value($this->filters['segment'] ?? ''),

                Select::make('overdue')
                    ->title(tkey('crm.leads.filters.overdue'))
                    ->empty(tkey('crm.leads.filters.all_tasks'), '')
                    ->options([
                        '1' => tkey('crm.leads.filters.only_overdue'),
                    ])
                    ->value($this->filters['overdue'] ?? ''),

                Select::make('only_due_today')
                    ->title(tkey('crm.leads.filters.only_due_today'))
                    ->empty(tkey('common.status.no'), '')
                    ->options(['1' => tkey('common.status.yes')])
                    ->value($this->filters['only_due_today'] ?? ''),

                Select::make('only_my')
                    ->title(tkey('crm.leads.filters.only_my'))
                    ->empty(tkey('common.status.no'), '')
                    ->options(['1' => tkey('common.status.yes')])
                    ->value($this->filters['only_my'] ?? ''),

                Select::make('only_unassigned')
                    ->title(tkey('crm.leads.filters.only_unassigned'))
                    ->empty(tkey('common.status.no'), '')
                    ->options(['1' => tkey('common.status.yes')])
                    ->value($this->filters['only_unassigned'] ?? ''),

                Select::make('only_duplicates')
                    ->title(tkey('crm.leads.filters.only_duplicates'))
                    ->empty(tkey('common.status.no'), '')
                    ->options(['1' => tkey('common.status.yes')])
                    ->value($this->filters['only_duplicates'] ?? ''),

                Select::make('only_open')
                    ->title(tkey('crm.leads.filters.only_open'))
                    ->empty(tkey('common.status.no'), '')
                    ->options(['1' => tkey('common.status.yes')])
                    ->value($this->filters['only_open'] ?? ''),

                Select::make('only_closed')
                    ->title(tkey('crm.leads.filters.only_closed'))
                    ->empty(tkey('common.status.no'), '')
                    ->options(['1' => tkey('common.status.yes')])
                    ->value($this->filters['only_closed'] ?? ''),

                Select::make('only_converted')
                    ->title(tkey('crm.leads.filters.only_converted'))
                    ->empty(tkey('common.status.no'), '')
                    ->options(['1' => tkey('common.status.yes')])
                    ->value($this->filters['only_converted'] ?? ''),

                Select::make('only_not_converted')
                    ->title(tkey('crm.leads.filters.only_not_converted'))
                    ->empty(tkey('common.status.no'), '')
                    ->options(['1' => tkey('common.status.yes')])
                    ->value($this->filters['only_not_converted'] ?? ''),

                Input::make('utm_source')
                    ->title(tkey('crm.leads.filters.utm_source'))
                    ->value($this->filters['utm_source'] ?? '')
                    ->canSee($this->canViewMarketing()),

                Input::make('utm_medium')
                    ->title(tkey('crm.leads.filters.utm_medium'))
                    ->value($this->filters['utm_medium'] ?? '')
                    ->canSee($this->canViewMarketing()),

                Input::make('utm_campaign')
                    ->title(tkey('crm.leads.filters.utm_campaign'))
                    ->value($this->filters['utm_campaign'] ?? '')
                    ->canSee($this->canViewMarketing()),

                Input::make('form_name')
                    ->title(tkey('crm.leads.filters.form_name'))
                    ->value($this->filters['form_name'] ?? ''),

                Input::make('created_from')
                    ->type('date')
                    ->title(tkey('crm.leads.filters.created_from'))
                    ->value($this->filters['created_from'] ?? ''),

                Input::make('created_to')
                    ->type('date')
                    ->title(tkey('crm.leads.filters.created_to'))
                    ->value($this->filters['created_to'] ?? ''),

                Input::make('next_follow_up_from')
                    ->type('date')
                    ->title(tkey('crm.leads.filters.next_follow_up_from'))
                    ->value($this->filters['next_follow_up_from'] ?? ''),

                Input::make('next_follow_up_to')
                    ->type('date')
                    ->title(tkey('crm.leads.filters.next_follow_up_to'))
                    ->value($this->filters['next_follow_up_to'] ?? ''),

                Input::make('last_contacted_from')
                    ->type('date')
                    ->title(tkey('crm.leads.filters.last_contacted_from'))
                    ->value($this->filters['last_contacted_from'] ?? ''),

                Input::make('last_contacted_to')
                    ->type('date')
                    ->title(tkey('crm.leads.filters.last_contacted_to'))
                    ->value($this->filters['last_contacted_to'] ?? ''),

                Button::make(tkey('common.actions.search'))
                    ->icon('bs.search')
                    ->method('filter')
                    ->novalidate(),
            ]),

            Layout::table('leads', [
                TD::make('id', tkey('crm.leads.columns.id'))
                    ->render(fn (MarketingLead $lead): string => (string) $lead->id)
                    ->alignCenter(),
                TD::make('lead_number', tkey('crm.leads.fields.lead_number'))
                    ->render(fn (MarketingLead $lead): string => $lead->lead_number ?? '-'),
                TD::make('name', tkey('crm.leads.columns.full_name'))
                    ->render(fn (MarketingLead $lead): string => (string) Link::make($lead->fullName())
                        ->route($this->leadEditRoute(), $lead)),
                TD::make('phone', tkey('crm.leads.columns.phone'))
                    ->render(fn (MarketingLead $lead): string => $lead->phone ?? '-'),
                TD::make('email', tkey('crm.leads.columns.email'))
                    ->render(fn (MarketingLead $lead): string => $lead->email ?? '-'),
                TD::make('messenger', tkey('crm.leads.columns.messenger'))
                    ->render(fn (MarketingLead $lead): string => $lead->messenger ?? '-'),
                TD::make('city', tkey('crm.leads.columns.city'))
                    ->render(fn (MarketingLead $lead): string => $lead->city ?? '-'),
                TD::make('campaign', tkey('crm.leads.columns.campaign'))
                    ->render(fn (MarketingLead $lead): string => $lead->marketingCampaign?->name ?? '-')
                    ->canSee($this->canViewMarketing()),
                TD::make('branch', tkey('crm.leads.columns.branch'))
                    ->render(fn (MarketingLead $lead): string => $lead->branch?->displayName() ?? '-'),
                TD::make('course', tkey('crm.leads.columns.course'))
                    ->render(fn (MarketingLead $lead): string => $lead->trainingProgram?->displayTitle() ?? '-'),
                TD::make('group', tkey('crm.leads.columns.training_group'))
                    ->render(fn (MarketingLead $lead): string => $lead->trainingGroup?->displayName() ?? '-'),
                TD::make('source', tkey('crm.leads.columns.source'))
                    ->render(fn (MarketingLead $lead): string => $this->sourceLabels[$lead->source] ?? LeadSource::translatedLabel($lead->source)),
                TD::make('license_category', tkey('crm.leads.columns.category'))
                    ->render(fn (MarketingLead $lead): string => $lead->license_category ?? '-')
                    ->alignCenter(),
                TD::make('manager', tkey('crm.leads.columns.manager'))
                    ->render(fn (MarketingLead $lead): string => $lead->responsibleManager?->name ?? '-'),
                TD::make('tags', tkey('crm.leads.columns.tags'))
                    ->render(fn (MarketingLead $lead): string => $lead->tags->map->displayName()->join(', ') ?: '-'),
                TD::make('next_follow_up_at', tkey('crm.leads.columns.next_follow_up'))
                    ->render(fn (MarketingLead $lead): string => $lead->is_overdue
                        ? tkey('crm.tasks.labels.overdue_value', ['value' => $lead->next_follow_up_at?->format('Y-m-d H:i') ?? '-'])
                        : ($lead->next_follow_up_at?->format('Y-m-d H:i') ?? '-')),
                TD::make('created_at', tkey('crm.leads.columns.created_at'))
                    ->render(fn (MarketingLead $lead): string => $lead->created_at->format('Y-m-d H:i')),
                TD::make('budget', tkey('crm.leads.columns.budget'))
                    ->render(fn (MarketingLead $lead): string => $lead->budgetForHumans()),
                TD::make('status', tkey('crm.leads.columns.status'))
                    ->render(fn (MarketingLead $lead): string => collect([
                        $lead->status->label(),
                        $lead->is_duplicate ? tkey('crm.leads.statuses.duplicate') : null,
                        $lead->is_spam ? tkey('crm.leads.statuses.spam') : null,
                        $lead->is_lost ? tkey('crm.leads.statuses.lost') : null,
                        $lead->is_converted ? tkey('crm.leads.segments.converted') : null,
                    ])->filter()->unique()->join(' / ')),
                TD::make('duplicate', tkey('crm.leads.columns.duplicate'))
                    ->render(fn (MarketingLead $lead): string => $lead->duplicateOf
                        ? tkey('crm.leads.labels.duplicate_of', ['id' => $lead->duplicateOf->id])
                        : ($lead->duplicates_count > 0 ? tkey('crm.leads.labels.has_duplicates', ['count' => $lead->duplicates_count]) : '-')),
                TD::make('activity', tkey('crm.leads.columns.activity'))
                    ->render(fn (MarketingLead $lead): string => tkey('crm.leads.activity.summary', [
                        'communications' => $lead->communications_count,
                        'comments' => $lead->comments_count,
                        'documents' => $lead->documents_count,
                    ])),
                TD::make('converted', tkey('crm.leads.columns.converted'))
                    ->render(fn (MarketingLead $lead): string => $lead->convertedStudentProfile?->fullName() ?? '-'),
                TD::make('actions', tkey('crm.leads.columns.actions'))
                    ->cantHide()
                    ->alignRight()
                    ->render(fn (MarketingLead $lead): DropDown => DropDown::make()
                        ->icon('bs.three-dots-vertical')
                        ->list([
                            Link::make(tkey('crm.leads.actions.open'))
                                ->icon('bs.box-arrow-in-right')
                                ->route($this->leadEditRoute(), $lead),
                            Link::make(tkey('crm.leads.actions.edit'))
                                ->icon('bs.pencil')
                                ->route($this->leadEditRoute(), $lead),
                            Link::make(tkey('crm.leads.actions.change_status'))
                                ->icon('bs.arrow-repeat')
                                ->route($this->leadEditRoute(), $lead),
                            Link::make(tkey('crm.leads.actions.assign_manager'))
                                ->icon('bs.person-check')
                                ->route($this->leadEditRoute(), $lead),
                            Link::make(tkey('crm.leads.actions.add_note'))
                                ->icon('bs.chat-left-text')
                                ->route($this->leadEditRoute(), $lead),
                            Link::make(tkey('crm.leads.actions.log_call'))
                                ->icon('bs.telephone')
                                ->route($this->leadEditRoute(), $lead),
                            Link::make(tkey('crm.leads.actions.create_task'))
                                ->icon('bs.check2-square')
                                ->route($this->leadEditRoute(), $lead),
                            Link::make(tkey('crm.leads.actions.mark_lost'))
                                ->icon('bs.x-octagon')
                                ->route($this->leadEditRoute(), $lead),
                            Link::make(tkey('crm.leads.actions.mark_duplicate'))
                                ->icon('bs.files')
                                ->route($this->leadEditRoute(), $lead),
                            Link::make(tkey('crm.leads.actions.mark_spam'))
                                ->icon('bs.exclamation-octagon')
                                ->route($this->leadEditRoute(), $lead),
                        ])),
            ]),
        ];
    }

    public function filter(Request $request): RedirectResponse
    {
        return redirect()->route($this->leadIndexRoute(), array_filter([
            'search' => $request->input('search'),
            'status' => $request->input('status'),
            'source' => $request->input('source'),
            'manager_id' => $request->input('manager_id'),
            'branch_id' => $request->input('branch_id'),
            'training_program_id' => $request->input('training_program_id'),
            'course_category_id' => $request->input('course_category_id'),
            'training_group_id' => $request->input('training_group_id'),
            'tag_id' => $request->input('tag_id'),
            'lost_reason_code' => $request->input('lost_reason_code'),
            'priority' => $request->input('priority'),
            'segment' => $request->input('segment'),
            'utm_source' => $request->input('utm_source'),
            'utm_medium' => $request->input('utm_medium'),
            'utm_campaign' => $request->input('utm_campaign'),
            'form_name' => $request->input('form_name'),
            'created_from' => $request->input('created_from'),
            'created_to' => $request->input('created_to'),
            'next_follow_up_from' => $request->input('next_follow_up_from'),
            'next_follow_up_to' => $request->input('next_follow_up_to'),
            'last_contacted_from' => $request->input('last_contacted_from'),
            'last_contacted_to' => $request->input('last_contacted_to'),
            'overdue' => $request->input('overdue'),
            'only_due_today' => $request->input('only_due_today'),
            'only_my' => $request->input('only_my'),
            'only_unassigned' => $request->input('only_unassigned'),
            'only_duplicates' => $request->input('only_duplicates'),
            'only_open' => $request->input('only_open'),
            'only_closed' => $request->input('only_closed'),
            'only_converted' => $request->input('only_converted'),
            'only_not_converted' => $request->input('only_not_converted'),
        ], fn (mixed $value): bool => filled($value)));
    }

    public function export(ExportLeadsRequest $request, ExportLeadsCsvAction $exportLeads): StreamedResponse
    {
        $this->filters = $this->filtersFromRequest($request);

        return $exportLeads->handle($this->leadQuery($request), $request->user());
    }

    /**
     * @return array<string, string>
     */
    private function leadStatusOptions(): array
    {
        $labels = LeadStatusDictionary::translatedLabels();

        return $labels !== []
            ? $labels
            : collect(LeadStatus::cases())
                ->mapWithKeys(fn (LeadStatus $status): array => [
                    $status->value => $status->label(),
                ])
                ->all();
    }

    /**
     * @return array<string, string>
     */
    private function priorityOptions(): array
    {
        return [
            'low' => tkey('crm.leads.priorities.low'),
            'normal' => tkey('crm.leads.priorities.normal'),
            'high' => tkey('crm.leads.priorities.high'),
            'urgent' => tkey('crm.leads.priorities.urgent'),
        ];
    }

    private function leadQuery(Request $request): Builder
    {
        if ($this->filters === []) {
            $this->filters = $this->filtersFromRequest($request);
        }

        return MarketingLead::query()
            ->forLeadList()
            ->with([
                'branch:id,name,name_translations,city,city_translations',
                'marketingCampaign:id,name,channel',
                'responsibleManager:id,name',
                'trainingProgram:id,title,title_translations',
                'trainingGroup:id,name,name_translations,code',
                'convertedStudentProfile:id,first_name,last_name',
                'duplicateOf:id,first_name,last_name',
                'tags:id,slug,name,name_translations',
            ])
            ->withCount(['comments', 'communications', 'documents', 'overdueTasks', 'duplicates'])
            ->tap(fn (Builder $query): Builder => app(FilterLeadsAction::class)->handle($query, $this->filters, $request->user(), $this->canViewMarketing()));
    }

    /**
     * @return array<string, string>
     */
    private function segments(): array
    {
        return [
            'all' => tkey('crm.leads.segments.all'),
            'new' => tkey('crm.leads.segments.new'),
            'my' => tkey('crm.leads.segments.my'),
            'my_leads' => tkey('crm.leads.segments.my_leads'),
            'unassigned' => tkey('crm.leads.segments.unassigned'),
            'today' => tkey('crm.leads.segments.today'),
            'call_today' => tkey('crm.leads.segments.call_today'),
            'overdue' => tkey('crm.leads.segments.overdue'),
            'waiting_payment' => tkey('crm.leads.segments.waiting_payment'),
            'waiting_documents' => tkey('crm.leads.segments.waiting_documents'),
            'hot' => tkey('crm.leads.segments.hot'),
            'duplicate' => tkey('crm.leads.segments.duplicate'),
            'duplicates' => tkey('crm.leads.segments.duplicates'),
            'lost' => tkey('crm.leads.segments.lost'),
            'spam' => tkey('crm.leads.segments.spam'),
            'converted' => tkey('crm.leads.segments.converted'),
            'ready_to_enroll' => tkey('crm.leads.segments.ready_to_enroll'),
            'open' => tkey('crm.leads.segments.open'),
            'closed' => tkey('crm.leads.segments.closed'),
            'not_converted' => tkey('crm.leads.segments.not_converted'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function filtersFromRequest(Request $request): array
    {
        return [
            'search' => $this->requestFilterValue($request, 'search'),
            'status' => $this->requestFilterValue($request, 'status'),
            'source' => $this->requestFilterValue($request, 'source'),
            'manager_id' => $this->requestFilterValue($request, 'manager_id'),
            'branch_id' => $this->requestFilterValue($request, 'branch_id'),
            'training_program_id' => $this->requestFilterValue($request, 'training_program_id', 'course_id'),
            'course_category_id' => $this->requestFilterValue($request, 'course_category_id'),
            'training_group_id' => $this->requestFilterValue($request, 'training_group_id'),
            'tag_id' => $this->requestFilterValue($request, 'tag_id'),
            'lost_reason_code' => $this->requestFilterValue($request, 'lost_reason_code', 'lost_reason'),
            'priority' => $this->requestFilterValue($request, 'priority'),
            'created_from' => $this->requestFilterValue($request, 'created_from'),
            'created_to' => $this->requestFilterValue($request, 'created_to'),
            'next_follow_up_from' => $this->requestFilterValue($request, 'next_follow_up_from', 'follow_up_from'),
            'next_follow_up_to' => $this->requestFilterValue($request, 'next_follow_up_to', 'follow_up_to'),
            'last_contacted_from' => $this->requestFilterValue($request, 'last_contacted_from'),
            'last_contacted_to' => $this->requestFilterValue($request, 'last_contacted_to'),
            'utm_source' => $this->requestFilterValue($request, 'utm_source'),
            'utm_medium' => $this->requestFilterValue($request, 'utm_medium'),
            'utm_campaign' => $this->requestFilterValue($request, 'utm_campaign'),
            'form_name' => $this->requestFilterValue($request, 'form_name'),
            'overdue' => $this->requestFilterValue($request, 'overdue'),
            'only_due_today' => $this->requestFilterValue($request, 'only_due_today', 'due_today'),
            'only_my' => $this->requestFilterValue($request, 'only_my'),
            'only_unassigned' => $this->requestFilterValue($request, 'only_unassigned'),
            'only_duplicates' => $this->requestFilterValue($request, 'only_duplicates'),
            'only_open' => $this->requestFilterValue($request, 'only_open'),
            'only_closed' => $this->requestFilterValue($request, 'only_closed'),
            'only_converted' => $this->requestFilterValue($request, 'only_converted'),
            'only_not_converted' => $this->requestFilterValue($request, 'only_not_converted'),
            'segment' => $this->requestFilterValue($request, 'segment'),
        ];
    }

    private function requestFilterValue(Request $request, string ...$keys): string
    {
        foreach ($keys as $key) {
            $value = $request->query($key, $request->input($key));

            if (filled($value)) {
                return trim((string) $value);
            }
        }

        return '';
    }

    private function leadIndexRoute(): string
    {
        return request()->routeIs('platform.marketing.*')
            ? 'platform.marketing.leads'
            : 'platform.crm.leads';
    }

    private function leadCreateRoute(): string
    {
        return request()->routeIs('platform.marketing.*')
            ? 'platform.marketing.leads.create'
            : 'platform.crm.leads.create';
    }

    private function leadEditRoute(): string
    {
        return request()->routeIs('platform.marketing.*')
            ? 'platform.marketing.leads.edit'
            : 'platform.crm.leads.edit';
    }

    private function pipelineRoute(): string
    {
        return request()->routeIs('platform.marketing.*')
            ? 'platform.marketing.pipeline'
            : 'platform.crm.pipeline';
    }

    private function canViewMarketing(): bool
    {
        return request()->user()?->hasAccess('crm.leads.view_marketing') ?? false;
    }
}
