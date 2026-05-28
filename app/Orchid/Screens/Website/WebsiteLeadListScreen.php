<?php

namespace App\Orchid\Screens\Website;

use App\Models\Branch;
use App\Models\Course;
use App\Models\LeadSource;
use App\Models\MarketingLead;
use App\Models\TrainingGroup;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class WebsiteLeadListScreen extends Screen
{
    /**
     * @var array<string, mixed>
     */
    private array $filters = [];

    /**
     * @var array<int, string>
     */
    private array $courses = [];

    /**
     * @var array<int, string>
     */
    private array $branches = [];

    /**
     * @var array<int, string>
     */
    private array $groups = [];

    /**
     * @var array<string, string>
     */
    private array $sourceLabels = [];

    private bool $canViewMarketing = false;

    public function query(Request $request): iterable
    {
        $this->filters = $this->filtersFromRequest($request);
        $user = $request->user();
        $this->canViewMarketing = ($user?->hasAccess('website.view_marketing') ?? false)
            || ($user?->hasAccess('crm.leads.view_marketing') ?? false);

        $this->courses = Course::query()
            ->select(['id', 'title', 'title_translations', 'name_translations', 'slug', 'sort_order'])
            ->ordered()
            ->limit(100)
            ->get()
            ->mapWithKeys(fn (Course $course): array => [$course->id => $course->displayTitle()])
            ->all();
        $this->branches = Branch::query()
            ->forAdminList()
            ->ordered()
            ->limit(100)
            ->get()
            ->mapWithKeys(fn (Branch $branch): array => [$branch->id => $branch->displayName()])
            ->all();
        $this->groups = TrainingGroup::query()
            ->operationalList()
            ->ordered()
            ->limit(100)
            ->get()
            ->mapWithKeys(fn (TrainingGroup $group): array => [$group->id => $group->displayName()])
            ->all();

        $leads = $this->leadQuery()
            ->orderByDesc('created_at')
            ->simplePaginate(15)
            ->withQueryString();

        $this->sourceLabels = LeadSource::translatedLabels();

        return [
            'leads' => $leads,
        ];
    }

    public function name(): ?string
    {
        return tkey('website.admin.leads.title');
    }

    public function description(): ?string
    {
        return tkey('website.admin.leads.description');
    }

    public function permission(): iterable
    {
        return ['website.view_leads'];
    }

    public function layout(): iterable
    {
        return [
            Layout::rows($this->filterFields()),

            Layout::table('leads', $this->leadColumns()),
        ];
    }

    public function filter(Request $request): RedirectResponse
    {
        return redirect()->route('platform.website.leads', array_filter([
            'search' => $request->input('search'),
            'course_id' => $request->input('course_id'),
            'branch_id' => $request->input('branch_id'),
            'training_group_id' => $request->input('training_group_id'),
            'form_name' => $request->input('form_name'),
            'utm_source' => $request->input('utm_source'),
            'created_from' => $request->input('created_from'),
            'created_to' => $request->input('created_to'),
        ], fn (mixed $value): bool => filled($value)));
    }

    /**
     * @return array<int, mixed>
     */
    private function filterFields(): array
    {
        $fields = [
            Input::make('search')
                ->title(tkey('website.admin.filters.search'))
                ->value($this->filters['search'] ?? ''),
            Select::make('course_id')
                ->title(tkey('website.forms.fields.course'))
                ->empty(tkey('website.admin.filters.all_courses'), '')
                ->options($this->courses)
                ->value($this->filters['course_id'] ?? ''),
            Select::make('branch_id')
                ->title(tkey('website.forms.fields.branch'))
                ->empty(tkey('website.admin.filters.all_branches'), '')
                ->options($this->branches)
                ->value($this->filters['branch_id'] ?? ''),
            Select::make('training_group_id')
                ->title(tkey('website.forms.fields.training_group'))
                ->empty(tkey('website.admin.filters.all_groups'), '')
                ->options($this->groups)
                ->value($this->filters['training_group_id'] ?? ''),
            Input::make('form_name')
                ->title(tkey('website.forms.fields.form_name'))
                ->value($this->filters['form_name'] ?? ''),
        ];

        if ($this->canViewMarketing) {
            $fields[] = Input::make('utm_source')
                ->title(tkey('crm.leads.fields.utm_source'))
                ->value($this->filters['utm_source'] ?? '');
        }

        return [
            ...$fields,
            Input::make('created_from')
                ->type('date')
                ->title(tkey('crm.leads.filters.created_from'))
                ->value($this->filters['created_from'] ?? ''),
            Input::make('created_to')
                ->type('date')
                ->title(tkey('crm.leads.filters.created_to'))
                ->value($this->filters['created_to'] ?? ''),
            Button::make(tkey('common.actions.search'))
                ->icon('bs.search')
                ->method('filter')
                ->novalidate(),
        ];
    }

    /**
     * @return array<int, TD>
     */
    private function leadColumns(): array
    {
        $columns = [
            TD::make('name', tkey('crm.leads.columns.full_name'))
                ->render(fn (MarketingLead $lead): string => (string) Link::make($lead->fullName())
                    ->route('platform.marketing.leads.edit', $lead)),
            TD::make('phone', tkey('crm.leads.columns.phone'))
                ->render(fn (MarketingLead $lead): string => $lead->phone ?? '-'),
            TD::make('email', tkey('crm.leads.columns.email'))
                ->render(fn (MarketingLead $lead): string => $lead->email ?? '-'),
            TD::make('course', tkey('website.groups.fields.course'))
                ->render(fn (MarketingLead $lead): string => $lead->course?->displayTitle() ?? '-'),
            TD::make('branch', tkey('website.groups.fields.branch'))
                ->render(fn (MarketingLead $lead): string => $lead->branch?->displayName() ?? '-'),
            TD::make('group', tkey('website.forms.fields.training_group'))
                ->render(fn (MarketingLead $lead): string => $lead->trainingGroup?->displayName() ?? '-'),
            TD::make('source', tkey('crm.leads.columns.source'))
                ->render(fn (MarketingLead $lead): string => $this->sourceLabel($lead->source)),
            TD::make('form_name', tkey('website.forms.fields.form_name'))
                ->render(fn (MarketingLead $lead): string => $this->formNameLabel($lead->form_name)),
        ];

        if ($this->canViewMarketing) {
            $columns[] = TD::make('form_page', tkey('crm.leads.fields.form_page'))
                ->render(fn (MarketingLead $lead): string => $this->short($lead->form_page));
            $columns[] = TD::make('landing_page', tkey('crm.leads.fields.landing_page'))
                ->render(fn (MarketingLead $lead): string => $this->short($lead->landing_page));
            $columns[] = TD::make('utm_source', tkey('crm.leads.fields.utm_source'))
                ->render(fn (MarketingLead $lead): string => $lead->utm_source ?? '-');
            $columns[] = TD::make('utm_medium', tkey('crm.leads.fields.utm_medium'))
                ->render(fn (MarketingLead $lead): string => $lead->utm_medium ?? '-');
            $columns[] = TD::make('utm_campaign', tkey('crm.leads.fields.utm_campaign'))
                ->render(fn (MarketingLead $lead): string => $lead->utm_campaign ?? '-');
        }

        return [
            ...$columns,
            TD::make('created_at', tkey('crm.leads.columns.created_at'))
                ->render(fn (MarketingLead $lead): string => $lead->created_at->format('Y-m-d H:i')),
            TD::make('actions', tkey('crm.leads.columns.actions'))
                ->alignRight()
                ->render(fn (MarketingLead $lead): string => (string) Link::make(tkey('crm.leads.actions.open'))
                    ->icon('bs.box-arrow-in-right')
                    ->route('platform.marketing.leads.edit', $lead)),
        ];
    }

    private function leadQuery(): Builder
    {
        return MarketingLead::query()
            ->forLeadList()
            ->with([
                'branch:id,name,name_translations,city,city_translations',
                'course:id,title,title_translations,name_translations,slug',
                'trainingGroup:id,name,name_translations,code',
            ])
            ->whereIn('source', ['website', 'callback', 'contact_form', 'contact'])
            ->when($this->filters['search'] !== '', fn (Builder $query): Builder => $query->matchingSearch($this->filters['search']))
            ->when($this->filters['course_id'] !== '', fn (Builder $query): Builder => $query->where('training_program_id', $this->filters['course_id']))
            ->when($this->filters['branch_id'] !== '', fn (Builder $query): Builder => $query->where('branch_id', $this->filters['branch_id']))
            ->when($this->filters['training_group_id'] !== '', fn (Builder $query): Builder => $query->where('training_group_id', $this->filters['training_group_id']))
            ->when($this->filters['form_name'] !== '', fn (Builder $query): Builder => $query->where('form_name', $this->filters['form_name']))
            ->when($this->canViewMarketing && $this->filters['utm_source'] !== '', fn (Builder $query): Builder => $query->where('utm_source', $this->filters['utm_source']))
            ->when($this->filters['created_from'] !== '', fn (Builder $query): Builder => $query->whereDate('created_at', '>=', $this->filters['created_from']))
            ->when($this->filters['created_to'] !== '', fn (Builder $query): Builder => $query->whereDate('created_at', '<=', $this->filters['created_to']));
    }

    /**
     * @return array<string, string>
     */
    private function filtersFromRequest(Request $request): array
    {
        return [
            'search' => trim((string) $request->query('search')),
            'course_id' => trim((string) $request->query('course_id')),
            'branch_id' => trim((string) $request->query('branch_id')),
            'training_group_id' => trim((string) $request->query('training_group_id')),
            'form_name' => trim((string) $request->query('form_name')),
            'utm_source' => trim((string) $request->query('utm_source')),
            'created_from' => trim((string) $request->query('created_from')),
            'created_to' => trim((string) $request->query('created_to')),
        ];
    }

    private function short(?string $value): string
    {
        return filled($value) ? Str::limit($value, 64, '...') : '-';
    }

    private function sourceLabel(?string $source): string
    {
        if (! filled($source)) {
            return '-';
        }

        if (isset($this->sourceLabels[$source])) {
            return $this->sourceLabels[$source];
        }

        $translationKey = 'crm.leads.sources.'.$source;
        $translated = tkey($translationKey);

        return $translated !== $translationKey
            ? $translated
            : LeadSource::translatedLabel($source);
    }

    private function formNameLabel(?string $formName): string
    {
        if (! filled($formName)) {
            return '-';
        }

        return match ($formName) {
            'application', 'enrollment', 'website_application' => tkey('website.forms.apply.title'),
            'callback' => tkey('website.forms.callback.title'),
            'contact' => tkey('website.forms.contact.title'),
            default => Str::of($formName)->replace(['_', '-'], ' ')->title()->toString(),
        };
    }
}
