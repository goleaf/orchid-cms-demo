<?php

declare(strict_types=1);

namespace App\Orchid\Screens\School;

use App\Actions\ArchiveStudentAction;
use App\Actions\ExportStudentsCsvAction;
use App\Actions\FilterStudentsAction;
use App\Enums\EnrollmentStatus;
use App\Enums\StudentStatus;
use App\Http\Requests\Students\ArchiveStudentRequest;
use App\Http\Requests\Students\ExportStudentsRequest;
use App\Models\Branch;
use App\Models\CourseCategory;
use App\Models\Student;
use App\Models\StudentEnrollment;
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
use Orchid\Support\Facades\Toast;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StudentListScreen extends Screen
{
    /**
     * @var array<string, mixed>
     */
    private array $filters = [];

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
    private array $managers = [];

    /**
     * @var array<int, string>
     */
    private array $administrators = [];

    public function query(Request $request): iterable
    {
        $this->filters = $this->filtersFromRequest($request);

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
        $this->managers = User::query()
            ->select(['id', 'name'])
            ->orderBy('name')
            ->limit(100)
            ->pluck('name', 'id')
            ->all();
        $this->administrators = $this->managers;

        return [
            'students' => $this->studentQuery($request)
                ->orderByDesc('created_at')
                ->simplePaginate(15)
                ->withQueryString(),
        ];
    }

    public function name(): ?string
    {
        return tkey('students.title');
    }

    public function description(): ?string
    {
        return tkey('students.description');
    }

    public function permission(): iterable
    {
        return ['students.view', 'platform.crm.students'];
    }

    public function commandBar(): iterable
    {
        return [
            Link::make(tkey('students.actions.create'))
                ->icon('bs.plus-circle')
                ->route('platform.students.create')
                ->canSee($this->hasStudentAccess('students.create')),

            Link::make(tkey('menu.students.tasks'))
                ->icon('bs.check2-square')
                ->route('platform.students.tasks')
                ->canSee($this->hasStudentAccess('students.manage_tasks')),

            Button::make(tkey('common.actions.export_csv'))
                ->icon('bs.download')
                ->method('export')
                ->canSee($this->hasStudentAccess('students.export')),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::rows([
                Input::make('search')
                    ->title(tkey('students.filters.search'))
                    ->value($this->filters['search'] ?? ''),

                Select::make('segment')
                    ->title(tkey('students.filters.segment'))
                    ->empty(tkey('students.filters.no_segment'), '')
                    ->options($this->segments())
                    ->value($this->filters['segment'] ?? ''),

                Select::make('status')
                    ->title(tkey('students.filters.status'))
                    ->empty(tkey('students.filters.all_statuses'), '')
                    ->options($this->studentStatusOptions())
                    ->value($this->filters['status'] ?? ''),

                Select::make('enrollment_status')
                    ->title(tkey('students.filters.enrollment_status'))
                    ->empty(tkey('students.filters.all_enrollment_statuses'), '')
                    ->options($this->enrollmentStatusOptions())
                    ->value($this->filters['enrollment_status'] ?? ''),

                Select::make('training_program_id')
                    ->title(tkey('students.filters.course'))
                    ->empty(tkey('students.filters.all_courses'), '')
                    ->options($this->programs)
                    ->value($this->filters['training_program_id'] ?? ''),

                Select::make('course_category_id')
                    ->title(tkey('students.filters.course_category'))
                    ->empty(tkey('students.filters.all_course_categories'), '')
                    ->options($this->categories)
                    ->value($this->filters['course_category_id'] ?? ''),

                Select::make('branch_id')
                    ->title(tkey('students.filters.branch'))
                    ->empty(tkey('students.filters.all_branches'), '')
                    ->options($this->branches)
                    ->value($this->filters['branch_id'] ?? ''),

                Select::make('training_group_id')
                    ->title(tkey('students.filters.training_group'))
                    ->empty(tkey('students.filters.all_groups'), '')
                    ->options($this->groups)
                    ->value($this->filters['training_group_id'] ?? ''),

                Select::make('manager_id')
                    ->title(tkey('students.filters.manager'))
                    ->empty(tkey('students.filters.all_managers'), '')
                    ->options($this->managers)
                    ->value($this->filters['manager_id'] ?? ''),

                Select::make('administrator_id')
                    ->title(tkey('students.filters.administrator'))
                    ->empty(tkey('students.filters.all_administrators'), '')
                    ->options($this->administrators)
                    ->value($this->filters['administrator_id'] ?? ''),

                Input::make('created_from')
                    ->type('date')
                    ->title(tkey('students.filters.created_from'))
                    ->value($this->filters['created_from'] ?? ''),

                Input::make('created_to')
                    ->type('date')
                    ->title(tkey('students.filters.created_to'))
                    ->value($this->filters['created_to'] ?? ''),

                Select::make('only_active')
                    ->title(tkey('students.filters.only_active'))
                    ->empty(tkey('students.filters.no_segment'), '')
                    ->options($this->booleanFilterOptions())
                    ->value($this->filters['only_active'] ?? ''),

                Select::make('only_archived')
                    ->title(tkey('students.filters.only_archived'))
                    ->empty(tkey('students.filters.no_segment'), '')
                    ->options($this->booleanFilterOptions())
                    ->value($this->filters['only_archived'] ?? ''),

                Select::make('only_blocked')
                    ->title(tkey('students.filters.only_blocked'))
                    ->empty(tkey('students.filters.no_segment'), '')
                    ->options($this->booleanFilterOptions())
                    ->value($this->filters['only_blocked'] ?? ''),

                Select::make('only_with_active_enrollment')
                    ->title(tkey('students.filters.only_with_active_enrollment'))
                    ->empty(tkey('students.filters.no_segment'), '')
                    ->options($this->booleanFilterOptions())
                    ->value($this->filters['only_with_active_enrollment'] ?? ''),

                Select::make('only_without_active_enrollment')
                    ->title(tkey('students.filters.only_without_active_enrollment'))
                    ->empty(tkey('students.filters.no_segment'), '')
                    ->options($this->booleanFilterOptions())
                    ->value($this->filters['only_without_active_enrollment'] ?? ''),

                Select::make('only_without_group')
                    ->title(tkey('students.filters.only_without_group'))
                    ->empty(tkey('students.filters.no_segment'), '')
                    ->options($this->booleanFilterOptions())
                    ->value($this->filters['only_without_group'] ?? ''),

                Select::make('only_waiting_documents')
                    ->title(tkey('students.filters.only_waiting_documents'))
                    ->empty(tkey('students.filters.no_segment'), '')
                    ->options($this->booleanFilterOptions())
                    ->value($this->filters['only_waiting_documents'] ?? ''),

                Select::make('only_waiting_payment')
                    ->title(tkey('students.filters.only_waiting_payment'))
                    ->empty(tkey('students.filters.no_segment'), '')
                    ->options($this->booleanFilterOptions())
                    ->value($this->filters['only_waiting_payment'] ?? ''),

                Select::make('only_waiting_start')
                    ->title(tkey('students.filters.only_waiting_start'))
                    ->empty(tkey('students.filters.no_segment'), '')
                    ->options($this->booleanFilterOptions())
                    ->value($this->filters['only_waiting_start'] ?? ''),

                Button::make(tkey('common.actions.search'))
                    ->icon('bs.search')
                    ->method('filter')
                    ->novalidate(),
            ])->title(tkey('students.sections.overview')),

            Layout::table('students', [
                TD::make('student_number', tkey('students.fields.student_number'))
                    ->render(fn (Student $student): string => (string) Link::make($student->student_number ?: (string) $student->id)
                        ->route('platform.students.edit', $student)),
                TD::make('full_name', tkey('students.fields.full_name'))
                    ->render(fn (Student $student): string => $this->studentNameLabel($student)),
                TD::make('phone', tkey('students.fields.phone'))
                    ->render(fn (Student $student): string => $student->phone ?? '-'),
                TD::make('email', tkey('students.fields.email'))
                    ->render(fn (Student $student): string => $student->email ?? '-'),
                TD::make('current_course', tkey('students.enrollments.fields.course'))
                    ->render(fn (Student $student): string => $student->current_enrollment?->trainingProgram?->displayTitle() ?? '-'),
                TD::make('current_branch', tkey('students.enrollments.fields.branch'))
                    ->render(fn (Student $student): string => $student->current_enrollment?->branch?->displayName() ?? $student->branch?->displayName() ?? '-'),
                TD::make('current_group', tkey('students.enrollments.fields.training_group'))
                    ->render(fn (Student $student): string => $student->current_enrollment?->trainingGroup?->displayName() ?? $this->missingGroupLabel($student)),
                TD::make('student_status', tkey('students.fields.status'))
                    ->render(fn (Student $student): string => $this->studentStatusLabel($student)),
                TD::make('enrollment_status', tkey('students.enrollments.fields.status'))
                    ->render(fn (Student $student): string => $this->enrollmentStatusLabel($student->current_enrollment)),
                TD::make('manager', tkey('students.fields.manager'))
                    ->render(fn (Student $student): string => $student->manager?->name ?? '-'),
                TD::make('created_at', tkey('students.fields.created_at'))
                    ->render(fn (Student $student): string => $student->created_at?->format('Y-m-d H:i') ?? '-'),
                TD::make('actions', tkey('crm.leads.columns.actions'))
                    ->alignRight()
                    ->render(fn (Student $student): DropDown => DropDown::make()
                        ->icon('bs.three-dots-vertical')
                        ->list([
                            Link::make(tkey('students.actions.open'))
                                ->icon('bs.box-arrow-in-right')
                                ->route('platform.students.edit', $student),
                            Link::make(tkey('students.actions.add_enrollment'))
                                ->icon('bs.plus-square')
                                ->route('platform.students.enrollments.create', ['student_id' => $student->id])
                                ->canSee($this->hasStudentAccess('students.manage_enrollments')),
                            Button::make(tkey('students.actions.archive'))
                                ->icon('bs.archive')
                                ->method('archive')
                                ->parameters(['student' => $student->id])
                                ->confirm(tkey('students.messages.archive_confirm'))
                                ->canSee($this->hasStudentAccess('students.archive') && ! $student->is_archived),
                        ])),
            ]),
        ];
    }

    public function filter(Request $request): RedirectResponse
    {
        return redirect()->route('platform.students', array_filter([
            'search' => $request->input('search'),
            'segment' => $request->input('segment'),
            'status' => $request->input('status'),
            'enrollment_status' => $request->input('enrollment_status'),
            'training_program_id' => $request->input('training_program_id'),
            'course_category_id' => $request->input('course_category_id'),
            'branch_id' => $request->input('branch_id'),
            'training_group_id' => $request->input('training_group_id'),
            'manager_id' => $request->input('manager_id'),
            'administrator_id' => $request->input('administrator_id'),
            'created_from' => $request->input('created_from'),
            'created_to' => $request->input('created_to'),
            'only_active' => $request->input('only_active'),
            'only_archived' => $request->input('only_archived'),
            'only_blocked' => $request->input('only_blocked'),
            'only_with_active_enrollment' => $request->input('only_with_active_enrollment'),
            'only_without_active_enrollment' => $request->input('only_without_active_enrollment'),
            'only_without_group' => $request->input('only_without_group'),
            'only_waiting_documents' => $request->input('only_waiting_documents'),
            'only_waiting_payment' => $request->input('only_waiting_payment'),
            'only_waiting_start' => $request->input('only_waiting_start'),
        ], fn (mixed $value): bool => filled($value)));
    }

    public function export(ExportStudentsRequest $request, ExportStudentsCsvAction $exportStudents): StreamedResponse
    {
        $this->filters = $this->filtersFromRequest($request);

        return $exportStudents->handle($this->studentQuery($request), $request->user());
    }

    public function archive(ArchiveStudentRequest $request, ArchiveStudentAction $archiveStudent): RedirectResponse
    {
        $student = Student::query()->findOrFail((int) $request->input('student'));

        $archiveStudent->handle($student, $request->user(), $request->boolean('override_active_enrollment'));

        Toast::info(tkey('students.messages.archived'));

        return redirect()->route('platform.students', $request->query());
    }

    private function studentQuery(Request $request): Builder
    {
        if ($this->filters === []) {
            $this->filters = $this->filtersFromRequest($request);
        }

        $query = Student::query()
            ->forCrmList()
            ->with([
                'branch:id,name,name_translations,city,city_translations',
                'manager:id,name',
                'currentEnrollment' => fn ($query) => $query
                    ->select([
                        'enrollments.id',
                        'enrollments.uuid',
                        'enrollments.enrollment_number',
                        'enrollments.student_profile_id',
                        'enrollments.training_program_id',
                        'enrollments.branch_id',
                        'enrollments.training_group_id',
                        'enrollments.status',
                        'enrollments.status_id',
                        'enrollments.start_date',
                    ])
                    ->with([
                        'trainingProgram:id,title,title_translations,license_category',
                        'branch:id,name,name_translations,city,city_translations',
                        'trainingGroup:id,name,name_translations,code',
                    ]),
            ])
            ->tap(fn (Builder $query): Builder => app(FilterStudentsAction::class)->handle($query, $this->filters, $request->user()));

        return $query;
    }

    /**
     * @return array<string, string>
     */
    private function segments(): array
    {
        return collect([
            'all',
            'active',
            'new',
            'waiting_documents',
            'waiting_payment',
            'waiting_start',
            'without_group',
            'in_training',
            'paused',
            'completed',
            'archived',
        ])->mapWithKeys(fn (string $segment): array => [$segment => tkey('students.segments.'.$segment)])->all();
    }

    /**
     * @return array<string, string>
     */
    private function studentStatusOptions(): array
    {
        return collect(StudentStatus::cases())
            ->mapWithKeys(fn (StudentStatus $status): array => [$status->value => tkey('students.statuses.'.$status->value)])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    private function enrollmentStatusOptions(): array
    {
        return collect(EnrollmentStatus::cases())
            ->mapWithKeys(fn (EnrollmentStatus $status): array => [$status->value => tkey('students.enrollments.statuses.'.$status->value)])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    private function booleanFilterOptions(): array
    {
        return [
            '1' => tkey('common.status.yes'),
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
            'enrollment_status' => $this->requestFilterValue($request, 'enrollment_status'),
            'training_program_id' => $this->requestFilterValue($request, 'training_program_id', 'course_id'),
            'course_category_id' => $this->requestFilterValue($request, 'course_category_id'),
            'branch_id' => $this->requestFilterValue($request, 'branch_id'),
            'training_group_id' => $this->requestFilterValue($request, 'training_group_id'),
            'manager_id' => $this->requestFilterValue($request, 'manager_id'),
            'administrator_id' => $this->requestFilterValue($request, 'administrator_id'),
            'created_from' => $this->requestFilterValue($request, 'created_from'),
            'created_to' => $this->requestFilterValue($request, 'created_to'),
            'only_active' => $this->requestFilterValue($request, 'only_active'),
            'only_archived' => $this->requestFilterValue($request, 'only_archived'),
            'only_blocked' => $this->requestFilterValue($request, 'only_blocked'),
            'only_with_active_enrollment' => $this->requestFilterValue($request, 'only_with_active_enrollment'),
            'only_without_active_enrollment' => $this->requestFilterValue($request, 'only_without_active_enrollment'),
            'only_without_group' => $this->requestFilterValue($request, 'only_without_group'),
            'only_waiting_documents' => $this->requestFilterValue($request, 'only_waiting_documents'),
            'only_waiting_payment' => $this->requestFilterValue($request, 'only_waiting_payment'),
            'only_waiting_start' => $this->requestFilterValue($request, 'only_waiting_start'),
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

    private function studentNameLabel(Student $student): string
    {
        return collect([
            $student->display_name,
            $student->is_blocked ? tkey('students.badges.blocked') : null,
            $student->is_archived ? tkey('students.badges.archived') : null,
        ])->filter()->join(' / ');
    }

    private function studentStatusLabel(Student $student): string
    {
        return tkey('students.statuses.'.$student->status->value);
    }

    private function enrollmentStatusLabel(?StudentEnrollment $enrollment): string
    {
        if ($enrollment === null) {
            return '-';
        }

        $label = tkey('students.enrollments.statuses.'.$enrollment->status->value);
        $badges = collect([
            $enrollment->status === EnrollmentStatus::WaitingDocuments ? tkey('students.badges.waiting_documents') : null,
            $enrollment->status === EnrollmentStatus::WaitingPayment ? tkey('students.badges.waiting_payment') : null,
        ])->filter()->join(' / ');

        return $badges === '' ? $label : $label.' / '.$badges;
    }

    private function missingGroupLabel(Student $student): string
    {
        return $student->current_enrollment === null
            ? '-'
            : tkey('students.warnings.missing_group');
    }

    private function hasStudentAccess(string $permission): bool
    {
        return request()->user()?->hasAnyAccess([$permission, 'platform.crm.students']) ?? false;
    }
}
