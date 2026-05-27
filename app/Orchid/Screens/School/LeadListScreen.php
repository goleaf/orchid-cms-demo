<?php

declare(strict_types=1);

namespace App\Orchid\Screens\School;

use App\Enums\LeadStatus;
use App\Models\Branch;
use App\Models\LeadSource;
use App\Models\LeadTag;
use App\Models\MarketingLead;
use App\Models\TrainingGroup;
use App\Models\TrainingProgram;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

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
    private array $groups = [];

    /**
     * @var array<int, string>
     */
    private array $tags = [];

    public function query(Request $request): iterable
    {
        $this->filters = [
            'search' => trim((string) $request->query('search')),
            'status' => trim((string) $request->query('status')),
            'source' => trim((string) $request->query('source')),
            'manager_id' => trim((string) $request->query('manager_id')),
            'branch_id' => trim((string) $request->query('branch_id')),
            'training_program_id' => trim((string) $request->query('training_program_id')),
            'training_group_id' => trim((string) $request->query('training_group_id')),
            'tag_id' => trim((string) $request->query('tag_id')),
            'created_from' => trim((string) $request->query('created_from')),
            'created_to' => trim((string) $request->query('created_to')),
            'follow_up_from' => trim((string) $request->query('follow_up_from')),
            'follow_up_to' => trim((string) $request->query('follow_up_to')),
            'utm_source' => trim((string) $request->query('utm_source')),
            'utm_campaign' => trim((string) $request->query('utm_campaign')),
            'overdue' => trim((string) $request->query('overdue')),
            'segment' => trim((string) $request->query('segment')),
        ];

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
        return ['platform.marketing.leads', 'website.view_leads'];
    }

    public function commandBar(): iterable
    {
        return [
            Link::make(tkey('crm.leads.actions.create'))
                ->icon('bs.plus-circle')
                ->route('platform.marketing.leads.create'),

            Link::make(tkey('crm.leads.actions.open_pipeline'))
                ->icon('bs.kanban')
                ->route('platform.marketing.pipeline'),

            Link::make(tkey('menu.crm.tasks'))
                ->icon('bs.check2-square')
                ->route('platform.crm.tasks'),

            Button::make(tkey('common.actions.export_csv'))
                ->icon('bs.download')
                ->method('export'),
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

                Select::make('training_group_id')
                    ->title(tkey('crm.leads.filters.group'))
                    ->empty(tkey('crm.leads.filters.all_groups'), '')
                    ->options($this->groups)
                    ->value($this->filters['training_group_id'] ?? ''),

                Select::make('tag_id')
                    ->title(tkey('crm.leads.filters.tag'))
                    ->empty(tkey('crm.leads.filters.all_tags'), '')
                    ->options($this->tags)
                    ->value($this->filters['tag_id'] ?? ''),

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

                Input::make('utm_source')
                    ->title(tkey('crm.leads.fields.utm_source'))
                    ->value($this->filters['utm_source'] ?? ''),

                Input::make('utm_campaign')
                    ->title(tkey('crm.leads.fields.utm_campaign'))
                    ->value($this->filters['utm_campaign'] ?? ''),

                Button::make(tkey('common.actions.search'))
                    ->icon('bs.search')
                    ->method('filter')
                    ->novalidate(),
            ]),

            Layout::table('leads', [
                TD::make('id', tkey('crm.leads.columns.id'))
                    ->render(fn (MarketingLead $lead): string => (string) $lead->id)
                    ->alignCenter(),
                TD::make('name', tkey('crm.leads.columns.full_name'))
                    ->render(fn (MarketingLead $lead): string => (string) Link::make($lead->fullName())
                        ->route('platform.marketing.leads.edit', $lead)),
                TD::make('phone', tkey('crm.leads.columns.phone'))
                    ->render(fn (MarketingLead $lead): string => $lead->phone ?? '-'),
                TD::make('email', tkey('crm.leads.columns.email'))
                    ->render(fn (MarketingLead $lead): string => $lead->email ?? '-'),
                TD::make('messenger', tkey('crm.leads.columns.messenger'))
                    ->render(fn (MarketingLead $lead): string => $lead->messenger ?? '-'),
                TD::make('city', tkey('crm.leads.columns.city'))
                    ->render(fn (MarketingLead $lead): string => $lead->city ?? '-'),
                TD::make('campaign', tkey('crm.leads.columns.campaign'))
                    ->render(fn (MarketingLead $lead): string => $lead->marketingCampaign?->name ?? '-'),
                TD::make('branch', tkey('crm.leads.columns.branch'))
                    ->render(fn (MarketingLead $lead): string => $lead->branch?->name ?? '-'),
                TD::make('course', tkey('crm.leads.columns.course'))
                    ->render(fn (MarketingLead $lead): string => $lead->trainingProgram?->title ?? '-'),
                TD::make('source', tkey('crm.leads.columns.source'))
                    ->render(fn (MarketingLead $lead): string => $this->sourceLabels[$lead->source] ?? LeadSource::translatedLabel($lead->source)),
                TD::make('license_category', tkey('crm.leads.columns.category'))
                    ->render(fn (MarketingLead $lead): string => $lead->license_category ?? '-')
                    ->alignCenter(),
                TD::make('manager', tkey('crm.leads.columns.manager'))
                    ->render(fn (MarketingLead $lead): string => $lead->responsibleManager?->name ?? '-'),
                TD::make('next_follow_up_at', tkey('crm.leads.columns.next_follow_up'))
                    ->render(fn (MarketingLead $lead): string => $lead->next_follow_up_at?->format('Y-m-d H:i') ?? '-'),
                TD::make('created_at', tkey('crm.leads.columns.created_at'))
                    ->render(fn (MarketingLead $lead): string => $lead->created_at->format('Y-m-d H:i')),
                TD::make('budget', tkey('crm.leads.columns.budget'))
                    ->render(fn (MarketingLead $lead): string => $lead->budgetForHumans()),
                TD::make('status', tkey('crm.leads.columns.status'))
                    ->render(fn (MarketingLead $lead): string => $lead->status->label()),
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
                    ->render(fn (MarketingLead $lead): string => (string) Link::make(tkey('crm.leads.actions.open'))
                        ->icon('bs.box-arrow-in-right')
                        ->route('platform.marketing.leads.edit', $lead)),
            ]),
        ];
    }

    public function filter(Request $request): RedirectResponse
    {
        return redirect()->route('platform.marketing.leads', array_filter([
            'search' => $request->input('search'),
            'status' => $request->input('status'),
            'source' => $request->input('source'),
            'manager_id' => $request->input('manager_id'),
            'overdue' => $request->input('overdue'),
        ], fn (mixed $value): bool => filled($value)));
    }

    /**
     * @return array<string, string>
     */
    private function leadStatusOptions(): array
    {
        return collect(LeadStatus::cases())
            ->mapWithKeys(fn (LeadStatus $status): array => [
                $status->value => $status->label(),
            ])
            ->all();
    }
}
