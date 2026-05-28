<?php

declare(strict_types=1);

namespace App\Orchid\Screens\School;

use App\Actions\ArchiveStudentAction;
use App\Enums\EnrollmentStatus;
use App\Enums\StudentStatus;
use App\Http\Requests\Students\ArchiveStudentRequest;
use App\Models\Branch;
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
    private array $groups = [];

    /**
     * @var array<int, string>
     */
    private array $managers = [];

    public function query(Request $request): iterable
    {
        $this->filters = [
            'search' => (string) $request->query('search', ''),
            'status' => (string) $request->query('status', ''),
            'enrollment_status' => (string) $request->query('enrollment_status', ''),
            'training_program_id' => (string) $request->query('training_program_id', ''),
            'branch_id' => (string) $request->query('branch_id', ''),
            'training_group_id' => (string) $request->query('training_group_id', ''),
            'manager_id' => (string) $request->query('manager_id', ''),
            'created_from' => (string) $request->query('created_from', ''),
            'created_to' => (string) $request->query('created_to', ''),
            'segment' => (string) $request->query('segment', ''),
        ];

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
        $this->managers = User::query()
            ->select(['id', 'name'])
            ->orderBy('name')
            ->limit(100)
            ->pluck('name', 'id')
            ->all();

        return [
            'students' => $this->studentQuery()
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

                Input::make('created_from')
                    ->type('date')
                    ->title(tkey('students.filters.created_from'))
                    ->value($this->filters['created_from'] ?? ''),

                Input::make('created_to')
                    ->type('date')
                    ->title(tkey('students.filters.created_to'))
                    ->value($this->filters['created_to'] ?? ''),

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
            'branch_id' => $request->input('branch_id'),
            'training_group_id' => $request->input('training_group_id'),
            'manager_id' => $request->input('manager_id'),
            'created_from' => $request->input('created_from'),
            'created_to' => $request->input('created_to'),
        ], fn (mixed $value): bool => filled($value)));
    }

    public function archive(ArchiveStudentRequest $request, ArchiveStudentAction $archiveStudent): RedirectResponse
    {
        $student = Student::query()->findOrFail((int) $request->input('student'));

        $archiveStudent->handle($student, $request->user(), $request->boolean('override_active_enrollment'));

        Toast::info(tkey('students.messages.archived'));

        return redirect()->route('platform.students', $request->query());
    }

    private function studentQuery(): Builder
    {
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
            ->search($this->filters['search'] ?? null)
            ->byStatus($this->filters['status'] ?? null)
            ->byManager($this->filters['manager_id'] ?? null)
            ->when($this->filters['created_from'] !== '', fn (Builder $query): Builder => $query->whereDate('created_at', '>=', $this->filters['created_from']))
            ->when($this->filters['created_to'] !== '', fn (Builder $query): Builder => $query->whereDate('created_at', '<=', $this->filters['created_to']))
            ->when($this->filters['enrollment_status'] !== '', fn (Builder $query): Builder => $query->whereHas(
                'enrollments',
                fn (Builder $enrollment): Builder => $enrollment->byStatus($this->filters['enrollment_status'])
            ))
            ->when($this->filters['training_program_id'] !== '', fn (Builder $query): Builder => $query->whereHas(
                'enrollments',
                fn (Builder $enrollment): Builder => $enrollment->byCourse($this->filters['training_program_id'])
            ))
            ->when($this->filters['branch_id'] !== '', fn (Builder $query): Builder => $query->where(function (Builder $query): void {
                $query->where('branch_id', $this->filters['branch_id'])
                    ->orWhereHas('enrollments', fn (Builder $enrollment): Builder => $enrollment->byBranch($this->filters['branch_id']));
            }))
            ->when($this->filters['training_group_id'] !== '', fn (Builder $query): Builder => $query->whereHas(
                'enrollments',
                fn (Builder $enrollment): Builder => $enrollment->byTrainingGroup($this->filters['training_group_id'])
            ));

        return $this->applySegment($query, (string) ($this->filters['segment'] ?? ''));
    }

    private function applySegment(Builder $query, string $segment): Builder
    {
        return match ($segment) {
            'active' => $query->active(),
            'new' => $query->whereDoesntHave('enrollments'),
            'waiting_documents' => $query->whereHas('enrollments', fn (Builder $enrollment): Builder => $enrollment->waitingDocuments()),
            'waiting_payment' => $query->whereHas('enrollments', fn (Builder $enrollment): Builder => $enrollment->waitingPayment()),
            'waiting_start' => $query->whereHas('enrollments', fn (Builder $enrollment): Builder => $enrollment->waitingStart()),
            'without_group' => $query->whereHas('activeEnrollments', fn (Builder $enrollment): Builder => $enrollment->whereNull('training_group_id')),
            'in_training' => $query->whereHas('enrollments', fn (Builder $enrollment): Builder => $enrollment->whereIn('status', [
                EnrollmentStatus::Active->value,
                EnrollmentStatus::Theory->value,
                EnrollmentStatus::Practice->value,
            ])),
            'paused' => $query->whereHas('enrollments', fn (Builder $enrollment): Builder => $enrollment->where('status', EnrollmentStatus::Paused->value)),
            'completed' => $query->whereHas('enrollments', fn (Builder $enrollment): Builder => $enrollment->completed()),
            'archived' => $query->archived(),
            default => $query,
        };
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
