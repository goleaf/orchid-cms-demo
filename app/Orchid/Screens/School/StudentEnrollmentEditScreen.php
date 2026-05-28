<?php

declare(strict_types=1);

namespace App\Orchid\Screens\School;

use App\Actions\AssignEnrollmentGroupAction;
use App\Actions\ChangeEnrollmentStatusAction;
use App\Actions\CreateStudentEnrollmentAction;
use App\Actions\UpdateStudentEnrollmentAction;
use App\Enums\EnrollmentStatus;
use App\Http\Requests\Students\AssignEnrollmentGroupRequest;
use App\Http\Requests\Students\ChangeEnrollmentStatusRequest;
use App\Http\Requests\Students\StudentEnrollmentRequest;
use App\Models\Branch;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\TrainingGroup;
use App\Models\TrainingProgram;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class StudentEnrollmentEditScreen extends Screen
{
    public ?StudentEnrollment $enrollment = null;

    /**
     * @var array<int, string>
     */
    private array $students = [];

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

    public function query(?StudentEnrollment $enrollment = null, ?Request $request = null): iterable
    {
        $request ??= request();

        $this->students = Student::query()
            ->forCrmList()
            ->orderByDesc('created_at')
            ->limit(100)
            ->get()
            ->mapWithKeys(fn (Student $student): array => [$student->id => $student->display_name])
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
        $this->managers = User::query()
            ->select(['id', 'name'])
            ->orderBy('name')
            ->limit(100)
            ->pluck('name', 'id')
            ->all();

        $studentId = $request->integer('student_id') ?: null;
        $this->enrollment = $enrollment?->exists
            ? StudentEnrollment::query()
                ->with([
                    'student:id,student_number,first_name,last_name,full_name,phone,email',
                    'lead:id,lead_number,first_name,last_name,phone,email',
                    'trainingProgram:id,title,title_translations,license_category',
                    'branch:id,name,name_translations,city,city_translations',
                    'trainingGroup:id,name,name_translations,code',
                    'manager:id,name',
                    'administrator:id,name',
                    'instructor:id,name',
                    'teacher:id,name',
                    'creator:id,name',
                    'updater:id,name',
                ])
                ->whereKey($enrollment->id)
                ->firstOrFail()
            : new StudentEnrollment([
                'student_profile_id' => $studentId,
                'status' => EnrollmentStatus::WaitingDocuments,
                'currency' => 'EUR',
                'payment_status' => 'pending',
                'theory_progress' => 0,
                'practice_progress' => 0,
            ]);

        return [
            'enrollment' => $this->enrollment,
            'enrollment.student_id' => $this->enrollment->student_profile_id,
            'enrollment.course_id' => $this->enrollment->training_program_id,
            'enrollment.status' => $this->enrollment->status instanceof EnrollmentStatus
                ? $this->enrollment->status->value
                : EnrollmentStatus::WaitingDocuments->value,
            'enrollment.created_by_name' => $this->enrollment->creator?->name,
            'enrollment.updated_by_name' => $this->enrollment->updater?->name,
        ];
    }

    public function name(): ?string
    {
        return $this->enrollment?->exists
            ? tkey('students.enrollments.edit_title')
            : tkey('students.enrollments.create_title');
    }

    public function description(): ?string
    {
        return tkey('students.enrollments.description');
    }

    public function permission(): iterable
    {
        return ['students.manage_enrollments', 'platform.crm.students'];
    }

    public function commandBar(): iterable
    {
        return [
            Link::make(tkey('common.actions.back'))
                ->icon('bs.arrow-left')
                ->route('platform.students'),

            Link::make(tkey('students.actions.open'))
                ->icon('bs.person-lines-fill')
                ->href($this->enrollment?->student ? route('platform.students.edit', $this->enrollment->student) : '#')
                ->canSee($this->enrollment?->student !== null),

            Link::make(tkey('students.actions.open_source_lead'))
                ->icon('bs.funnel')
                ->href($this->enrollment?->lead ? route('platform.crm.leads.edit', $this->enrollment->lead) : '#')
                ->canSee($this->enrollment?->lead !== null),

            Button::make(tkey('students.actions.save'))
                ->icon('bs.check2-circle')
                ->method('save'),

            ModalToggle::make(tkey('students.actions.change_enrollment_status'))
                ->icon('bs.arrow-repeat')
                ->modal('changeStatusModal')
                ->canSee($this->enrollment?->exists && $this->hasStudentAccess('students.enrollments.change_status')),

            ModalToggle::make(tkey('students.actions.assign_group'))
                ->icon('bs.people')
                ->modal('assignGroupModal')
                ->canSee($this->enrollment?->exists && $this->hasStudentAccess('students.manage_enrollments')),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::columns([
                Layout::rows([
                    Input::make('enrollment.id')->type('hidden'),
                    Input::make('enrollment.enrollment_number')
                        ->title(tkey('students.enrollments.fields.enrollment_number'))
                        ->disabled()
                        ->canSee($this->enrollment?->exists ?? false),
                    Select::make('enrollment.student_id')
                        ->title(tkey('students.enrollments.fields.student'))
                        ->options($this->students)
                        ->required(),
                    Input::make('enrollment.lead_id')
                        ->title(tkey('students.enrollments.fields.lead'))
                        ->disabled(),
                    Select::make('enrollment.status')
                        ->title(tkey('students.enrollments.fields.status'))
                        ->options($this->enrollmentStatusOptions())
                        ->required(),
                    Select::make('enrollment.manager_id')
                        ->title(tkey('students.enrollments.fields.manager'))
                        ->options($this->managers)
                        ->empty(tkey('students.empty.no_manager')),
                    Select::make('enrollment.administrator_id')
                        ->title(tkey('students.enrollments.fields.administrator'))
                        ->options($this->managers)
                        ->empty(tkey('students.empty.no_administrator')),
                    Select::make('enrollment.instructor_id')
                        ->title(tkey('students.enrollments.fields.instructor'))
                        ->options($this->managers)
                        ->empty(tkey('students.empty.no_instructor')),
                    Select::make('enrollment.teacher_id')
                        ->title(tkey('students.enrollments.fields.teacher'))
                        ->options($this->managers)
                        ->empty(tkey('students.empty.no_teacher')),
                ])->title(tkey('students.enrollments.sections.main_information')),

                Layout::rows([
                    Select::make('enrollment.training_program_id')
                        ->title(tkey('students.enrollments.fields.course'))
                        ->options($this->programs)
                        ->required(),
                    Select::make('enrollment.branch_id')
                        ->title(tkey('students.enrollments.fields.branch'))
                        ->options($this->branches)
                        ->empty(tkey('students.filters.all_branches')),
                    Select::make('enrollment.training_group_id')
                        ->title(tkey('students.enrollments.fields.training_group'))
                        ->options($this->groups)
                        ->empty(tkey('students.filters.all_groups')),
                    Input::make('enrollment.start_date')
                        ->title(tkey('students.enrollments.fields.start_date'))
                        ->type('date'),
                    Input::make('enrollment.planned_end_date')
                        ->title(tkey('students.enrollments.fields.planned_end_date'))
                        ->type('date'),
                    Input::make('enrollment.actual_end_date')
                        ->title(tkey('students.enrollments.fields.actual_end_date'))
                        ->type('date'),
                ])->title(tkey('students.enrollments.sections.course_and_branch')),
            ]),

            Layout::columns([
                Layout::rows([
                    Input::make('enrollment.preferred_time')
                        ->title(tkey('students.enrollments.fields.preferred_time')),
                    Select::make('enrollment.training_language')
                        ->title(tkey('students.enrollments.fields.training_language'))
                        ->options($this->languageOptions())
                        ->empty(tkey('students.filters.no_segment')),
                    Select::make('enrollment.format')
                        ->title(tkey('students.enrollments.fields.format'))
                        ->options($this->formatOptions())
                        ->empty(tkey('students.filters.no_segment')),
                    Select::make('enrollment.gearbox_type')
                        ->title(tkey('students.enrollments.fields.gearbox_type'))
                        ->options($this->gearboxOptions())
                        ->empty(tkey('students.filters.no_segment')),
                ])->title(tkey('students.enrollments.sections.training_preferences')),

                Layout::rows([
                    Input::make('enrollment.price')
                        ->title(tkey('students.enrollments.fields.price'))
                        ->type('number')
                        ->step('0.01'),
                    Input::make('enrollment.discount')
                        ->title(tkey('students.enrollments.fields.discount'))
                        ->type('number')
                        ->step('0.01'),
                    Input::make('enrollment.currency')
                        ->title(tkey('students.enrollments.fields.currency'))
                        ->maxlength(3),
                    Select::make('enrollment.payment_status')
                        ->title(tkey('students.enrollments.fields.payment_status'))
                        ->options($this->paymentStatusOptions())
                        ->empty(tkey('students.filters.no_segment')),
                    Input::make('enrollment.theory_progress')
                        ->title(tkey('students.enrollments.fields.theory_progress'))
                        ->type('number')
                        ->step('0.01')
                        ->disabled(),
                    Input::make('enrollment.practice_progress')
                        ->title(tkey('students.enrollments.fields.practice_progress'))
                        ->type('number')
                        ->step('0.01')
                        ->disabled(),
                ])->title(tkey('students.enrollments.sections.payment_placeholder')),
            ]),

            Layout::rows([
                TextArea::make('enrollment.notes')
                    ->title(tkey('students.enrollments.fields.notes'))
                    ->rows(4),
                TextArea::make('enrollment.internal_notes')
                    ->title(tkey('students.enrollments.fields.internal_notes'))
                    ->rows(4),
                Input::make('enrollment.created_by_name')
                    ->title(tkey('students.fields.created_by'))
                    ->disabled(),
                Input::make('enrollment.updated_by_name')
                    ->title(tkey('students.fields.updated_by'))
                    ->disabled(),
                Input::make('enrollment.created_at')
                    ->title(tkey('students.enrollments.fields.created_at'))
                    ->disabled(),
                Input::make('enrollment.updated_at')
                    ->title(tkey('students.enrollments.fields.updated_at'))
                    ->disabled(),
            ])->title(tkey('students.enrollments.sections.system_data')),

            Layout::modal('changeStatusModal', Layout::rows([
                Select::make('status')
                    ->title(tkey('students.enrollments.fields.status'))
                    ->options($this->enrollmentStatusOptions())
                    ->required(),
            ]))
                ->title(tkey('students.actions.change_enrollment_status'))
                ->method('changeStatus')
                ->applyButton(tkey('students.actions.change_enrollment_status'))
                ->canSee($this->enrollment?->exists ?? false),

            Layout::modal('assignGroupModal', Layout::rows([
                Select::make('training_group_id')
                    ->title(tkey('students.enrollments.fields.training_group'))
                    ->options($this->groups)
                    ->required(),
            ]))
                ->title(tkey('students.actions.assign_group'))
                ->method('assignGroup')
                ->applyButton(tkey('students.actions.assign_group'))
                ->canSee($this->enrollment?->exists ?? false),
        ];
    }

    public function save(
        StudentEnrollmentRequest $request,
        CreateStudentEnrollmentAction $createEnrollment,
        UpdateStudentEnrollmentAction $updateEnrollment,
    ): RedirectResponse {
        $payload = $request->enrollmentData();
        $enrollment = $request->routeEnrollment();

        if ($enrollment instanceof StudentEnrollment) {
            $enrollment = $updateEnrollment->handle($enrollment, $payload, $request->user(), $request->boolean('override_locked_enrollment'));
            Toast::info(tkey('students.messages.enrollment_updated'));

            return redirect()->route('platform.students.enrollments.edit', $enrollment);
        }

        $studentId = $payload['student_id'] ?? $payload['student_profile_id'] ?? null;
        $student = Student::query()->findOrFail($studentId);
        $enrollment = $createEnrollment->handle($student, $payload, $request->user(), $request->boolean('create_onboarding_tasks'));

        Toast::info(tkey('students.messages.enrollment_created'));

        return redirect()->route('platform.students.enrollments.edit', $enrollment);
    }

    public function changeStatus(StudentEnrollment $enrollment, ChangeEnrollmentStatusRequest $request, ChangeEnrollmentStatusAction $changeStatus): RedirectResponse
    {
        $changeStatus->handle($enrollment, $request->targetStatus(), $request->user(), $request->boolean('override_status_transition'));

        Toast::info(tkey('students.messages.enrollment_status_changed'));

        return redirect()->route('platform.students.enrollments.edit', $enrollment);
    }

    public function assignGroup(StudentEnrollment $enrollment, AssignEnrollmentGroupRequest $request, AssignEnrollmentGroupAction $assignGroup): RedirectResponse
    {
        $assignGroup->handle($enrollment, $request->groupId(), $request->user(), $request->boolean('allow_overbooking'));

        Toast::info(tkey('students.messages.group_assigned'));

        return redirect()->route('platform.students.enrollments.edit', $enrollment);
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
    private function languageOptions(): array
    {
        return [
            'ru' => 'RU',
            'en' => 'EN',
            'lt' => 'LT',
            'pl' => 'PL',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function formatOptions(): array
    {
        return collect(['offline', 'online', 'hybrid', 'individual', 'group'])
            ->mapWithKeys(fn (string $format): array => [$format => tkey('students.training_formats.'.$format)])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    private function gearboxOptions(): array
    {
        return collect(['manual', 'automatic', 'both', 'unknown'])
            ->mapWithKeys(fn (string $type): array => [$type => tkey('students.gearbox.'.$type)])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    private function paymentStatusOptions(): array
    {
        return collect(['not_required', 'pending', 'partially_paid', 'paid', 'overdue'])
            ->mapWithKeys(fn (string $status): array => [$status => tkey('students.payment_statuses.'.$status)])
            ->all();
    }

    private function hasStudentAccess(string $permission): bool
    {
        return request()->user()?->hasAnyAccess([$permission, 'platform.crm.students']) ?? false;
    }
}
