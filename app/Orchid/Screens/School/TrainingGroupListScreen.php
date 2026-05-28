<?php

declare(strict_types=1);

namespace App\Orchid\Screens\School;

use App\Actions\AddStudentToTrainingGroupAction;
use App\Actions\ArchiveTrainingGroupAction;
use App\Actions\ChangeTrainingGroupStatusAction;
use App\Actions\HideTrainingGroupFromSiteAction;
use App\Actions\PublishTrainingGroupOnSiteAction;
use App\Actions\RecalculateTrainingGroupCapacityAction;
use App\Http\Requests\Education\ArchiveTrainingGroupRequest;
use App\Http\Requests\Education\ChangeTrainingGroupStatusRequest;
use App\Http\Requests\Education\HideTrainingGroupRequest;
use App\Http\Requests\Education\PublishTrainingGroupRequest;
use App\Http\Requests\Education\TrainingGroupMembershipRequest;
use App\Models\Branch;
use App\Models\CourseCategory;
use App\Models\StudentEnrollment;
use App\Models\TrainingGroup;
use App\Models\TrainingGroupStatus;
use App\Models\TrainingProgram;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class TrainingGroupListScreen extends Screen
{
    /**
     * @var array<string, mixed>
     */
    private array $filters = [];

    /**
     * @var array<int, string>
     */
    private array $statuses = [];

    /**
     * @var array<int, string>
     */
    private array $courses = [];

    /**
     * @var array<int, string>
     */
    private array $categories = [];

    /**
     * @var array<int, string>
     */
    private array $branches = [];

    /**
     * @var array<int, string>
     */
    private array $users = [];

    /**
     * @var array<int, string>
     */
    private array $enrollments = [];

    public function query(Request $request): iterable
    {
        $this->filters = $this->filtersFromRequest($request);
        $this->loadOptions();

        return [
            'groups' => $this->groupQuery($request)
                ->orderBy('start_date')
                ->orderBy('starts_on')
                ->orderBy('id')
                ->simplePaginate(20)
                ->withQueryString(),
        ];
    }

    public function name(): ?string
    {
        return tkey('education.groups.title');
    }

    public function description(): ?string
    {
        return tkey('education.groups.description');
    }

    public function permission(): iterable
    {
        return ['education.groups.view'];
    }

    public function commandBar(): iterable
    {
        return [
            Link::make(tkey('education.groups.actions.create'))
                ->icon('bs.plus-circle')
                ->route('platform.education.groups.create')
                ->canSee($this->hasAccess('education.groups.create')),

            Link::make(tkey('education.groups.segments.recruiting'))
                ->icon('bs.person-plus')
                ->route('platform.education.groups', ['segment' => 'recruiting']),

            Link::make(tkey('education.groups.segments.scheduled'))
                ->icon('bs.calendar-event')
                ->route('platform.education.groups', ['segment' => 'scheduled']),

            Link::make(tkey('education.groups.segments.active'))
                ->icon('bs.play-circle')
                ->route('platform.education.groups', ['segment' => 'active']),

            Button::make(tkey('education.groups.actions.export_csv'))
                ->icon('bs.download')
                ->method('export')
                ->canSee($this->hasAccess('education.groups.export')),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::rows([
                Input::make('search')
                    ->title(tkey('education.groups.filters.search'))
                    ->value($this->filters['search'] ?? ''),

                Select::make('segment')
                    ->title(tkey('education.groups.filters.status'))
                    ->empty(tkey('education.groups.segments.all'), '')
                    ->options($this->segments())
                    ->value($this->filters['segment'] ?? ''),

                Select::make('status_id')
                    ->title(tkey('education.groups.filters.status'))
                    ->empty(tkey('education.groups.segments.all'), '')
                    ->options($this->statuses)
                    ->value($this->filters['status_id'] ?? ''),

                Select::make('course_id')
                    ->title(tkey('education.groups.filters.course'))
                    ->empty(tkey('education.groups.segments.all'), '')
                    ->options($this->courses)
                    ->value($this->filters['course_id'] ?? ''),

                Select::make('course_category_id')
                    ->title(tkey('education.groups.filters.course_category'))
                    ->empty(tkey('education.groups.segments.all'), '')
                    ->options($this->categories)
                    ->value($this->filters['course_category_id'] ?? ''),

                Select::make('branch_id')
                    ->title(tkey('education.groups.filters.branch'))
                    ->empty(tkey('education.groups.segments.all'), '')
                    ->options($this->branches)
                    ->value($this->filters['branch_id'] ?? ''),

                Select::make('manager_id')
                    ->title(tkey('education.groups.filters.manager'))
                    ->empty(tkey('education.groups.segments.all'), '')
                    ->options($this->users)
                    ->value($this->filters['manager_id'] ?? ''),

                Select::make('teacher_id')
                    ->title(tkey('education.groups.filters.teacher'))
                    ->empty(tkey('education.groups.segments.all'), '')
                    ->options($this->users)
                    ->value($this->filters['teacher_id'] ?? ''),

                Input::make('start_date_from')
                    ->type('date')
                    ->title(tkey('education.groups.filters.start_date_from'))
                    ->value($this->filters['start_date_from'] ?? ''),

                Input::make('start_date_to')
                    ->type('date')
                    ->title(tkey('education.groups.filters.start_date_to'))
                    ->value($this->filters['start_date_to'] ?? ''),

                Select::make('only_visible_on_site')
                    ->title(tkey('education.groups.filters.only_visible_on_site'))
                    ->empty(tkey('common.status.no'), '')
                    ->options($this->booleanOptions())
                    ->value($this->filters['only_visible_on_site'] ?? ''),

                Select::make('only_accepting_applications')
                    ->title(tkey('education.groups.filters.only_accepting_applications'))
                    ->empty(tkey('common.status.no'), '')
                    ->options($this->booleanOptions())
                    ->value($this->filters['only_accepting_applications'] ?? ''),

                Select::make('only_open_for_enrollment')
                    ->title(tkey('education.groups.filters.only_open_for_enrollment'))
                    ->empty(tkey('common.status.no'), '')
                    ->options($this->booleanOptions())
                    ->value($this->filters['only_open_for_enrollment'] ?? ''),

                Select::make('only_full')
                    ->title(tkey('education.groups.filters.only_full'))
                    ->empty(tkey('common.status.no'), '')
                    ->options($this->booleanOptions())
                    ->value($this->filters['only_full'] ?? ''),

                Select::make('only_almost_full')
                    ->title(tkey('education.groups.filters.only_almost_full'))
                    ->empty(tkey('common.status.no'), '')
                    ->options($this->booleanOptions())
                    ->value($this->filters['only_almost_full'] ?? ''),

                Button::make(tkey('common.actions.search'))
                    ->icon('bs.search')
                    ->method('filter')
                    ->novalidate(),
            ])->title(tkey('education.groups.sections.overview')),

            Layout::table('groups', [
                TD::make('group_number', tkey('education.groups.fields.group_number'))
                    ->render(fn (TrainingGroup $group): string => (string) Link::make($this->groupNumberLabel($group))
                        ->route('platform.education.groups.edit', $group)),
                TD::make('name', tkey('education.groups.fields.name'))
                    ->render(fn (TrainingGroup $group): string => $this->groupName($group)),
                TD::make('course', tkey('education.groups.fields.course'))
                    ->render(fn (TrainingGroup $group): string => $group->course?->displayTitle() ?? $group->trainingProgram?->displayTitle() ?? tkey('education.groups.empty.no_course')),
                TD::make('branch', tkey('education.groups.fields.branch'))
                    ->render(fn (TrainingGroup $group): string => $group->branch?->displayName() ?? tkey('education.groups.empty.no_branch')),
                TD::make('status', tkey('education.groups.fields.status'))
                    ->render(fn (TrainingGroup $group): string => $this->statusLabel($group)),
                TD::make('learning_program', tkey('education.groups.fields.learning_program'))
                    ->render(fn (TrainingGroup $group): string => $group->learningProgram?->display_name ?? tkey('education.groups.empty.no_program')),
                TD::make('start_date', tkey('education.groups.fields.start_date'))
                    ->render(fn (TrainingGroup $group): string => $group->start_date?->toDateString() ?? $group->starts_on?->toDateString() ?? '-'),
                TD::make('capacity', tkey('education.groups.sections.capacity'))
                    ->render(fn (TrainingGroup $group): string => $this->capacityLabel($group)),
                TD::make('available_places', tkey('education.groups.fields.available_places'))
                    ->render(fn (TrainingGroup $group): string => (string) $group->available_places)
                    ->alignCenter(),
                TD::make('manager', tkey('education.groups.fields.manager'))
                    ->render(fn (TrainingGroup $group): string => $group->manager?->name ?? '-'),
                TD::make('teacher', tkey('education.groups.fields.teacher'))
                    ->render(fn (TrainingGroup $group): string => $group->teacher?->name ?? $group->instructor?->name ?? '-'),
                TD::make('visible_on_site', tkey('education.groups.fields.is_visible_on_site'))
                    ->render(fn (TrainingGroup $group): string => $this->booleanLabel((bool) $group->is_visible_on_site)),
                TD::make('accepting_applications', tkey('education.groups.fields.is_accepting_applications'))
                    ->render(fn (TrainingGroup $group): string => $this->booleanLabel((bool) $group->is_accepting_applications)),
                TD::make('created_at', tkey('education.groups.fields.created_at'))
                    ->render(fn (TrainingGroup $group): string => $group->created_at?->format('Y-m-d H:i') ?? '-'),
                TD::make('actions', tkey('crm.leads.columns.actions'))
                    ->cantHide()
                    ->alignRight()
                    ->render(fn (TrainingGroup $group): DropDown => DropDown::make()
                        ->icon('bs.three-dots-vertical')
                        ->list([
                            Link::make(tkey('education.groups.actions.open'))
                                ->icon('bs.box-arrow-in-right')
                                ->route('platform.education.groups.edit', $group),
                            Link::make(tkey('education.groups.actions.edit'))
                                ->icon('bs.pencil')
                                ->route('platform.education.groups.edit', $group)
                                ->canSee($this->hasAccess('education.groups.update')),
                            ModalToggle::make(tkey('education.groups.actions.change_status'))
                                ->icon('bs.arrow-repeat')
                                ->modal('changeStatusModal')
                                ->method('changeStatus')
                                ->parameters(['group_id' => $group->id])
                                ->canSee($this->hasAccess('education.groups.change_status')),
                            ModalToggle::make(tkey('education.groups.actions.add_student'))
                                ->icon('bs.person-plus')
                                ->modal('addStudentModal')
                                ->method('addStudent')
                                ->parameters(['membership.training_group_id' => $group->id])
                                ->canSee($this->hasAccess('education.groups.manage_students')),
                            Button::make(tkey('education.groups.actions.recalculate_capacity'))
                                ->icon('bs.arrow-clockwise')
                                ->method('recalculateCapacity')
                                ->parameters(['group_id' => $group->id])
                                ->canSee($this->hasAccess('education.groups.update')),
                            Button::make(tkey('education.groups.actions.publish_on_site'))
                                ->icon('bs.eye')
                                ->method('publishOnSite')
                                ->parameters(['group_id' => $group->id])
                                ->confirm(tkey('education.groups.messages.publish_confirm'))
                                ->canSee($this->hasAccess('education.groups.manage_public_visibility') && ! $group->is_visible_on_site),
                            Button::make(tkey('education.groups.actions.hide_from_site'))
                                ->icon('bs.eye-slash')
                                ->method('hideFromSite')
                                ->parameters(['group_id' => $group->id])
                                ->confirm(tkey('education.groups.messages.hide_confirm'))
                                ->canSee($this->hasAccess('education.groups.manage_public_visibility') && $group->is_visible_on_site),
                            Button::make(tkey('education.groups.actions.archive'))
                                ->icon('bs.archive')
                                ->method('archive')
                                ->parameters(['group_id' => $group->id])
                                ->confirm(tkey('education.groups.messages.archive_confirm'))
                                ->canSee($this->hasAccess('education.groups.archive')),
                        ])),
            ]),

            Layout::modal('changeStatusModal', Layout::rows([
                Input::make('group_id')->type('hidden'),
                Select::make('status_id')
                    ->title(tkey('education.groups.fields.status'))
                    ->options($this->statuses)
                    ->required(),
                Select::make('override_status_transition')
                    ->title(tkey('permissions.education.groups.override_status_transition'))
                    ->empty(tkey('common.status.no'), '0')
                    ->options(['1' => tkey('common.status.yes')])
                    ->canSee($this->hasAccess('education.groups.override_status_transition')),
                Input::make('comment')
                    ->title(tkey('education.groups.memberships.fields.notes')),
            ]))
                ->title(tkey('education.groups.actions.change_status'))
                ->applyButton(tkey('education.groups.actions.change_status')),

            Layout::modal('addStudentModal', Layout::rows([
                Input::make('membership.training_group_id')->type('hidden'),
                Select::make('membership.enrollment_id')
                    ->title(tkey('education.groups.memberships.fields.enrollment'))
                    ->options($this->enrollments)
                    ->required(),
                Select::make('membership.allow_overbooking')
                    ->title(tkey('education.groups.fields.allow_overbooking'))
                    ->empty(tkey('common.status.no'), '0')
                    ->options(['1' => tkey('common.status.yes')]),
                Input::make('membership.notes')
                    ->title(tkey('education.groups.memberships.fields.notes')),
            ]))
                ->title(tkey('education.groups.actions.add_student'))
                ->applyButton(tkey('education.groups.actions.add_student')),
        ];
    }

    public function filter(Request $request): RedirectResponse
    {
        return redirect()->route('platform.education.groups', array_filter([
            'search' => $request->input('search'),
            'segment' => $request->input('segment'),
            'status_id' => $request->input('status_id'),
            'course_id' => $request->input('course_id'),
            'course_category_id' => $request->input('course_category_id'),
            'branch_id' => $request->input('branch_id'),
            'manager_id' => $request->input('manager_id'),
            'teacher_id' => $request->input('teacher_id'),
            'start_date_from' => $request->input('start_date_from'),
            'start_date_to' => $request->input('start_date_to'),
            'only_visible_on_site' => $request->input('only_visible_on_site'),
            'only_accepting_applications' => $request->input('only_accepting_applications'),
            'only_open_for_enrollment' => $request->input('only_open_for_enrollment'),
            'only_full' => $request->input('only_full'),
            'only_almost_full' => $request->input('only_almost_full'),
        ], fn (mixed $value): bool => filled($value)));
    }

    public function export(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->hasAccess('education.groups.export'), 403);

        Toast::info(tkey('education.groups.messages.export_queued'));

        return redirect()->route('platform.education.groups', $request->query());
    }

    public function changeStatus(
        ChangeTrainingGroupStatusRequest $request,
        ChangeTrainingGroupStatusAction $changeStatus,
    ): RedirectResponse {
        $group = TrainingGroup::query()->findOrFail($request->integer('group_id'));
        $changeStatus->handle($group, $request->integer('status_id'), $request->user(), $request->boolean('override_status_transition'), $request->input('comment'));

        Toast::info(tkey('education.groups.messages.status_changed'));

        return redirect()->route('platform.education.groups', $request->query());
    }

    public function addStudent(
        TrainingGroupMembershipRequest $request,
        AddStudentToTrainingGroupAction $addStudent,
    ): RedirectResponse {
        $data = $request->membershipData();
        $enrollment = StudentEnrollment::query()->findOrFail((int) $data['enrollment_id']);

        $addStudent->handle($enrollment, (int) $data['training_group_id'], $request->user(), (bool) $data['allow_overbooking']);

        Toast::info(tkey('education.groups.messages.student_added'));

        return redirect()->route('platform.education.groups', $request->query());
    }

    public function recalculateCapacity(Request $request, RecalculateTrainingGroupCapacityAction $recalculate): RedirectResponse
    {
        abort_unless($request->user()?->hasAnyAccess(['education.groups.update', 'education.groups.manage_students']), 403);

        $group = TrainingGroup::query()->findOrFail((int) $request->input('group_id'));
        $recalculate->handle($group, $request->user());

        Toast::info(tkey('education.groups.messages.capacity_recalculated'));

        return redirect()->route('platform.education.groups', $request->query());
    }

    public function publishOnSite(PublishTrainingGroupRequest $request, PublishTrainingGroupOnSiteAction $publish): RedirectResponse
    {
        $group = TrainingGroup::query()->findOrFail($request->integer('group_id'));
        $publish->handle($group, $request->user());

        Toast::info(tkey('education.groups.messages.published_on_site'));

        return redirect()->route('platform.education.groups', $request->query());
    }

    public function hideFromSite(HideTrainingGroupRequest $request, HideTrainingGroupFromSiteAction $hide): RedirectResponse
    {
        $group = TrainingGroup::query()->findOrFail($request->integer('group_id'));
        $hide->handle($group, $request->user());

        Toast::info(tkey('education.groups.messages.hidden_from_site'));

        return redirect()->route('platform.education.groups', $request->query());
    }

    public function archive(ArchiveTrainingGroupRequest $request, ArchiveTrainingGroupAction $archive): RedirectResponse
    {
        $group = TrainingGroup::query()->findOrFail($request->integer('group_id'));
        $archive->handle($group, $request->user(), $request->boolean('override_active_memberships'));

        Toast::info(tkey('education.groups.messages.archived'));

        return redirect()->route('platform.education.groups', $request->query());
    }

    private function groupQuery(Request $request): Builder
    {
        if ($this->filters === []) {
            $this->filters = $this->filtersFromRequest($request);
        }

        $query = TrainingGroup::query()
            ->operationalList()
            ->addSelect(['created_at', 'updated_at'])
            ->with([
                'branch:id,name,name_translations,city,city_translations',
                'course:id,title,title_translations,name_translations,license_category',
                'trainingProgram:id,title,title_translations,name_translations,license_category',
                'courseCategory:id,slug,code,name_translations',
                'statusRecord:id,code,name,name_translations,color,is_open_for_enrollment,is_archived,is_cancelled,is_success',
                'learningProgram:id,code,name_translations',
                'manager:id,name',
                'teacher:id,name',
                'instructor:id,name',
            ])
            ->withCount([
                'memberships',
                'activeMemberships',
                'schedulePatterns',
                'memberships as waitlist_count' => fn (Builder $query): Builder => $query->waitlisted(),
            ]);

        $query->search($this->filters['search'] ?? null);

        if (filled($this->filters['status_id'] ?? null)) {
            $query->where('status_id', $this->filters['status_id']);
        }

        if (filled($this->filters['course_id'] ?? null)) {
            $query->byCourse($this->filters['course_id']);
        }

        if (filled($this->filters['course_category_id'] ?? null)) {
            $query->byCourseCategory($this->filters['course_category_id']);
        }

        if (filled($this->filters['branch_id'] ?? null)) {
            $query->byBranch($this->filters['branch_id']);
        }

        if (filled($this->filters['manager_id'] ?? null)) {
            $query->byManager($this->filters['manager_id']);
        }

        if (filled($this->filters['teacher_id'] ?? null)) {
            $query->byTeacher($this->filters['teacher_id']);
        }

        if (filled($this->filters['start_date_from'] ?? null)) {
            $query->startsAfter($this->filters['start_date_from']);
        }

        if (filled($this->filters['start_date_to'] ?? null)) {
            $query->startsBefore($this->filters['start_date_to']);
        }

        if (($this->filters['only_visible_on_site'] ?? null) === '1') {
            $query->visibleOnSite();
        }

        if (($this->filters['only_accepting_applications'] ?? null) === '1') {
            $query->acceptingApplications();
        }

        if (($this->filters['only_open_for_enrollment'] ?? null) === '1') {
            $query->openForEnrollment();
        }

        if (($this->filters['only_full'] ?? null) === '1') {
            $query->where(function (Builder $query): void {
                $query->whereHas('statusRecord', fn (Builder $status): Builder => $status->where('code', 'full'))
                    ->orWhereColumn('capacity_taken', '>=', 'capacity_total');
            });
        }

        if (($this->filters['only_almost_full'] ?? null) === '1') {
            $query->whereHas('statusRecord', fn (Builder $status): Builder => $status->where('code', 'almost_full'));
        }

        $this->applySegment($query, (string) ($this->filters['segment'] ?? ''));

        return $query;
    }

    private function applySegment(Builder $query, string $segment): void
    {
        match ($segment) {
            'recruiting' => $query->whereHas('statusRecord', fn (Builder $status): Builder => $status->where('code', 'recruiting')),
            'almost_full' => $query->whereHas('statusRecord', fn (Builder $status): Builder => $status->where('code', 'almost_full')),
            'full' => $query->whereHas('statusRecord', fn (Builder $status): Builder => $status->where('code', 'full')),
            'scheduled' => $query->where(function (Builder $query): void {
                $query->where('status', 'planned')
                    ->orWhereHas('statusRecord', fn (Builder $status): Builder => $status->where('code', 'scheduled'));
            }),
            'active' => $query->active(),
            'completed' => $query->completed(),
            'cancelled' => $query->cancelled(),
            'archived' => $query->whereHas('statusRecord', fn (Builder $status): Builder => $status->where('is_archived', true)),
            'visible_on_site' => $query->visibleOnSite(),
            default => null,
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function filtersFromRequest(Request $request): array
    {
        return collect([
            'search',
            'segment',
            'status_id',
            'course_id',
            'course_category_id',
            'branch_id',
            'manager_id',
            'teacher_id',
            'start_date_from',
            'start_date_to',
            'only_visible_on_site',
            'only_accepting_applications',
            'only_open_for_enrollment',
            'only_full',
            'only_almost_full',
        ])
            ->mapWithKeys(fn (string $field): array => [$field => $request->input($field)])
            ->filter(fn (mixed $value): bool => filled($value))
            ->all();
    }

    private function loadOptions(): void
    {
        $this->statuses = TrainingGroupStatus::query()
            ->active()
            ->ordered()
            ->get(['id', 'code', 'name', 'name_translations'])
            ->mapWithKeys(fn (TrainingGroupStatus $status): array => [$status->id => $status->displayName()])
            ->all();

        $this->courses = TrainingProgram::query()
            ->forAcademyList()
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get(['id', 'title', 'title_translations', 'name_translations', 'license_category', 'sort_order'])
            ->mapWithKeys(fn (TrainingProgram $program): array => [$program->id => $program->displayTitle()])
            ->all();

        $this->categories = CourseCategory::query()
            ->active()
            ->ordered()
            ->get(['id', 'slug', 'code', 'name_translations'])
            ->mapWithKeys(fn (CourseCategory $category): array => [$category->id => $category->displayName()])
            ->all();

        $this->branches = Branch::query()
            ->forAdminList()
            ->orderBy('sort_order')
            ->orderBy('city')
            ->get()
            ->mapWithKeys(fn (Branch $branch): array => [$branch->id => $branch->displayName()])
            ->all();

        $this->users = User::query()
            ->select(['id', 'name'])
            ->orderBy('name')
            ->limit(200)
            ->pluck('name', 'id')
            ->all();

        $this->enrollments = StudentEnrollment::query()
            ->forAdminList()
            ->with([
                'student:id,first_name,last_name,full_name,student_number',
                'trainingProgram:id,title,title_translations,name_translations',
            ])
            ->active()
            ->limit(200)
            ->get()
            ->mapWithKeys(fn (StudentEnrollment $enrollment): array => [
                $enrollment->id => trim(($enrollment->enrollment_number ?? (string) $enrollment->id).' '.$enrollment->student?->display_name.' '.$enrollment->trainingProgram?->displayTitle()),
            ])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    private function segments(): array
    {
        return [
            'all' => tkey('education.groups.segments.all'),
            'recruiting' => tkey('education.groups.segments.recruiting'),
            'almost_full' => tkey('education.groups.segments.almost_full'),
            'full' => tkey('education.groups.segments.full'),
            'scheduled' => tkey('education.groups.segments.scheduled'),
            'active' => tkey('education.groups.segments.active'),
            'completed' => tkey('education.groups.segments.completed'),
            'cancelled' => tkey('education.groups.segments.cancelled'),
            'archived' => tkey('education.groups.segments.archived'),
            'visible_on_site' => tkey('education.groups.segments.visible_on_site'),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function booleanOptions(): array
    {
        return ['1' => tkey('common.status.yes')];
    }

    private function groupName(TrainingGroup $group): string
    {
        $warnings = collect([
            $group->course_id === null && $group->training_program_id === null ? tkey('education.groups.empty.no_course') : null,
            $group->branch_id === null ? tkey('education.groups.empty.no_branch') : null,
            (int) ($group->schedule_patterns_count ?? 0) === 0 ? tkey('education.groups.empty.no_schedule_patterns') : null,
            $group->is_visible_on_site && blank($group->getTranslation('public_description')) ? tkey('education.groups.empty.no_public_description') : null,
        ])->filter()->join(' / ');

        return trim($group->displayName().($warnings !== '' ? ' - '.$warnings : ''));
    }

    private function groupNumberLabel(TrainingGroup $group): string
    {
        $number = $group->group_number ?: (string) $group->id;

        return filled($group->code) && $group->code !== $number
            ? $number.' / '.$group->code
            : $number;
    }

    private function statusLabel(TrainingGroup $group): string
    {
        $status = $group->statusRecord;
        $label = $status?->displayName() ?? $group->status?->label() ?? '-';

        return collect([
            $label,
            $group->is_full ? tkey('education.groups.segments.full') : null,
            $group->is_almost_full ? tkey('education.groups.segments.almost_full') : null,
        ])->filter()->unique()->join(' / ');
    }

    private function capacityLabel(TrainingGroup $group): string
    {
        return collect([
            $group->capacity_taken.'/'.$group->capacity_total,
            tkey('education.groups.fields.capacity_waitlist').': '.$group->capacity_waitlist,
            $group->is_full ? tkey('education.groups.segments.full') : null,
            $group->is_almost_full ? tkey('education.groups.segments.almost_full') : null,
        ])->filter()->join(' / ');
    }

    private function booleanLabel(bool $value): string
    {
        return $value ? tkey('common.status.yes') : tkey('common.status.no');
    }

    private function hasAccess(string $permission): bool
    {
        return request()->user()?->hasAccess($permission) ?? false;
    }
}
