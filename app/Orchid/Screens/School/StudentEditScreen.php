<?php

declare(strict_types=1);

namespace App\Orchid\Screens\School;

use App\Actions\AddStudentNoteAction;
use App\Actions\ArchiveStudentAction;
use App\Actions\AssignStudentManagerAction;
use App\Actions\CancelStudentTaskAction;
use App\Actions\ChangeStudentStatusAction;
use App\Actions\CompleteStudentTaskAction;
use App\Actions\CreatePortalAccessPlaceholderAction;
use App\Actions\CreateStudentAction;
use App\Actions\CreateStudentEnrollmentAction;
use App\Actions\CreateStudentOnboardingTasksAction;
use App\Actions\CreateStudentTaskAction;
use App\Actions\UpdateStudentAction;
use App\Enums\CourseFormat;
use App\Enums\EnrollmentPaymentStatus;
use App\Enums\EnrollmentStatus;
use App\Enums\GearboxType;
use App\Enums\StudentStatus;
use App\Enums\StudentTaskPriority;
use App\Http\Requests\Students\AddStudentNoteRequest;
use App\Http\Requests\Students\ArchiveStudentRequest;
use App\Http\Requests\Students\AssignStudentManagerRequest;
use App\Http\Requests\Students\ChangeStudentStatusRequest;
use App\Http\Requests\Students\CompleteStudentTaskRequest;
use App\Http\Requests\Students\CreatePortalAccessRequest;
use App\Http\Requests\Students\StoreStudentEnrollmentRequest;
use App\Http\Requests\Students\StoreStudentTaskRequest;
use App\Http\Requests\Students\StudentRequest;
use App\Models\Branch;
use App\Models\Student;
use App\Models\StudentActivity;
use App\Models\StudentEnrollment;
use App\Models\StudentTask;
use App\Models\TrainingGroup;
use App\Models\TrainingProgram;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\CheckBox;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class StudentEditScreen extends Screen
{
    public ?Student $student = null;

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

    public function query(?Student $student = null): iterable
    {
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

        $this->student = $student?->exists
            ? Student::query()
                ->with([
                    'branch:id,name,name_translations,city,city_translations',
                    'user:id,name,email',
                    'manager:id,name',
                    'administrator:id,name',
                    'creator:id,name',
                    'updater:id,name',
                    'sourceLead:id,lead_number,first_name,last_name,phone,email,status,source,responsible_manager_id,created_at,converted_at,utm_source,utm_medium,utm_campaign,utm_content,utm_term,referrer_url,landing_page,form_page,form_name,ip_address,user_agent',
                    'sourceLead.responsibleManager:id,name',
                    'enrollments' => fn ($query) => $query
                        ->forAdminList()
                        ->with([
                            'trainingProgram:id,title,title_translations,license_category',
                            'branch:id,name,name_translations,city,city_translations',
                            'trainingGroup:id,name,name_translations,code',
                            'manager:id,name',
                        ])
                        ->latest(),
                    'tasks' => fn ($query) => $query
                        ->select(['id', 'student_id', 'enrollment_id', 'title_translations', 'description_translations', 'assigned_to_id', 'created_by_id', 'priority', 'status', 'due_at', 'completed_at', 'cancelled_at', 'created_at'])
                        ->with(['enrollment:id,enrollment_number,student_profile_id', 'assignedTo:id,name'])
                        ->latest('due_at')
                        ->limit(20),
                    'activities' => fn ($query) => $query
                        ->select(['id', 'student_id', 'enrollment_id', 'lead_id', 'user_id', 'type', 'title', 'body', 'old_value', 'new_value', 'meta', 'created_at'])
                        ->with('user:id,name')
                        ->latest()
                        ->limit(30),
                ])
                ->whereKey($student->id)
                ->firstOrFail()
            : new Student([
                'branch_id' => array_key_first($this->branches),
                'status' => StudentStatus::Active,
                'status_id' => null,
                'locale' => app()->getLocale(),
                'consent_accepted' => false,
            ]);

        $sourceLead = $this->student->sourceLead;

        return [
            'student' => $this->student,
            'student.status' => $this->student->status instanceof StudentStatus ? $this->student->status->value : StudentStatus::Active->value,
            'student.created_by_name' => $this->student->creator?->name,
            'student.updated_by_name' => $this->student->updater?->name,
            'student.source_lead_number' => $sourceLead?->lead_number,
            'student.source_lead_status' => $sourceLead?->status?->value,
            'student.source_lead_source' => $sourceLead?->source,
            'student.source_lead_manager' => $sourceLead?->responsibleManager?->name,
            'student.source_lead_created_at' => $sourceLead?->created_at?->format('Y-m-d H:i'),
            'student.source_lead_converted_at' => $sourceLead?->converted_at?->format('Y-m-d H:i'),
            'student.source_lead_utm_source' => $sourceLead?->utm_source,
            'student.source_lead_utm_medium' => $sourceLead?->utm_medium,
            'student.source_lead_utm_campaign' => $sourceLead?->utm_campaign,
            'student.source_lead_landing_page' => $sourceLead?->landing_page,
            'student.source_lead_form_page' => $sourceLead?->form_page,
            'student.source_lead_form_name' => $sourceLead?->form_name,
        ];
    }

    public function name(): ?string
    {
        return $this->student?->exists
            ? tkey('students.edit_title')
            : tkey('students.create_title');
    }

    public function description(): ?string
    {
        return tkey('students.description');
    }

    public function permission(): iterable
    {
        return ['students.create', 'students.update', 'platform.crm.students'];
    }

    public function commandBar(): iterable
    {
        return [
            Link::make(tkey('common.actions.back'))
                ->icon('bs.arrow-left')
                ->route('platform.students'),

            Button::make(tkey('students.actions.save'))
                ->icon('bs.check2-circle')
                ->method('save'),

            Button::make(tkey('students.actions.save_and_return'))
                ->icon('bs.check2-all')
                ->method('saveAndReturn'),

            ModalToggle::make(tkey('students.actions.change_status'))
                ->icon('bs.arrow-repeat')
                ->modal('changeStatusModal')
                ->canSee($this->student?->exists && $this->hasStudentAccess('students.change_status')),

            ModalToggle::make(tkey('students.actions.assign_manager'))
                ->icon('bs.person-check')
                ->modal('assignManagerModal')
                ->canSee($this->student?->exists && $this->hasStudentAccess('students.update')),

            ModalToggle::make(tkey('students.actions.add_note'))
                ->icon('bs.chat-left-text')
                ->modal('addNoteModal')
                ->canSee($this->student?->exists && $this->hasStudentAccess('students.update')),

            ModalToggle::make(tkey('students.actions.create_task'))
                ->icon('bs.check2-square')
                ->modal('createTaskModal')
                ->canSee($this->student?->exists && $this->hasStudentAccess('students.manage_tasks')),

            Button::make(tkey('students.actions.create_onboarding_tasks'))
                ->icon('bs.list-check')
                ->method('createOnboardingTasks')
                ->canSee($this->student?->exists && $this->hasStudentAccess('students.manage_tasks')),

            ModalToggle::make(tkey('students.actions.add_enrollment'))
                ->icon('bs.plus-square')
                ->modal('addEnrollmentModal')
                ->canSee($this->student?->exists && $this->hasStudentAccess('students.manage_enrollments')),

            Link::make(tkey('students.actions.open_source_lead'))
                ->icon('bs.link-45deg')
                ->href($this->student?->sourceLead !== null ? route('platform.crm.leads.edit', $this->student->sourceLead) : '#')
                ->canSee($this->student?->exists && $this->student?->sourceLead !== null && $this->canViewCrmSource()),

            Button::make(tkey('students.actions.create_portal_access'))
                ->icon('bs.person-gear')
                ->method('createPortalAccess')
                ->confirm(tkey('students.messages.portal_access_confirm'))
                ->canSee($this->student?->exists && $this->hasStudentAccess('students.update')),

            Button::make(tkey('students.actions.archive'))
                ->icon('bs.archive')
                ->method('archive')
                ->confirm(tkey('students.messages.archive_confirm'))
                ->canSee($this->student?->exists && $this->hasStudentAccess('students.archive') && ! $this->student?->is_archived),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::columns([
                Layout::rows([
                    Input::make('student.id')->type('hidden'),
                    Input::make('student.student_number')
                        ->title(tkey('students.fields.student_number'))
                        ->disabled()
                        ->canSee($this->student?->exists ?? false),
                    Input::make('student.full_name')
                        ->title(tkey('students.fields.full_name')),
                    Input::make('student.first_name')
                        ->title(tkey('students.fields.first_name'))
                        ->required(),
                    Input::make('student.last_name')
                        ->title(tkey('students.fields.last_name'))
                        ->required(),
                    Input::make('student.middle_name')
                        ->title(tkey('students.fields.middle_name')),
                    Input::make('student.date_of_birth')
                        ->type('date')
                        ->title(tkey('students.fields.date_of_birth')),
                    Input::make('student.personal_code')
                        ->title(tkey('students.fields.personal_code')),
                    Input::make('student.gender')
                        ->title(tkey('students.fields.gender')),
                ])->title(tkey('students.sections.personal_data')),

                Layout::rows([
                    Input::make('student.phone')
                        ->title(tkey('students.fields.phone')),
                    Input::make('student.email')
                        ->type('email')
                        ->title(tkey('students.fields.email')),
                    Input::make('student.preferred_messenger')
                        ->title(tkey('students.fields.preferred_messenger')),
                    Input::make('student.telegram_username')
                        ->title(tkey('students.fields.telegram_username')),
                    Input::make('student.whatsapp_phone')
                        ->title(tkey('students.fields.whatsapp_phone')),
                    Input::make('student.emergency_contact_name')
                        ->title(tkey('students.fields.emergency_contact_name')),
                    Input::make('student.emergency_contact_phone')
                        ->title(tkey('students.fields.emergency_contact_phone')),
                    Input::make('student.city')
                        ->title(tkey('students.fields.city')),
                    TextArea::make('student.address')
                        ->title(tkey('students.fields.address'))
                        ->rows(2),
                    Input::make('student.locale')
                        ->title(tkey('students.fields.locale')),
                ])->title(tkey('students.sections.contact_data')),
            ]),

            Layout::columns([
                Layout::rows([
                    Select::make('student.status')
                        ->title(tkey('students.fields.status'))
                        ->options($this->studentStatusOptions())
                        ->required(),
                    Select::make('student.branch_id')
                        ->title(tkey('students.enrollments.fields.branch'))
                        ->options($this->branches)
                        ->required(),
                    Select::make('student.manager_id')
                        ->title(tkey('students.fields.manager'))
                        ->options($this->managers)
                        ->empty(tkey('students.empty.no_manager')),
                    Select::make('student.administrator_id')
                        ->title(tkey('students.fields.administrator'))
                        ->options($this->managers)
                        ->empty(tkey('students.empty.no_administrator')),
                    TextArea::make('student.comment')
                        ->title(tkey('students.fields.comment'))
                        ->rows(3),
                    TextArea::make('student.internal_comment')
                        ->title(tkey('students.fields.internal_comment'))
                        ->rows(3),
                ])->title(tkey('students.sections.overview')),

                Layout::rows([
                    Input::make('student.user_id')
                        ->title(tkey('students.fields.user'))
                        ->disabled(),
                    Input::make('student.portal_access_created_at')
                        ->title(tkey('students.fields.portal_access_created_at'))
                        ->disabled(),
                    TextArea::make('student.documents_summary')
                        ->title(tkey('students.fields.documents_summary'))
                        ->rows(4)
                        ->value($this->summaryValue($this->student?->documents_summary))
                        ->disabled(),
                    TextArea::make('student.payment_summary')
                        ->title(tkey('students.fields.payment_summary'))
                        ->rows(4)
                        ->value($this->summaryValue($this->student?->payment_summary))
                        ->disabled(),
                    CheckBox::make('student.consent_accepted')
                        ->sendTrueOrFalse()
                        ->title(tkey('students.fields.consent_accepted'))
                        ->placeholder(tkey('students.fields.consent_accepted')),
                    Input::make('student.consent_accepted_at')
                        ->title(tkey('students.fields.consent_accepted_at'))
                        ->disabled(),
                    Input::make('student.consent_text_version')
                        ->title(tkey('students.fields.consent_text_version'))
                        ->disabled(),
                ])->title(tkey('students.sections.portal_access')),
            ]),

            Layout::rows([
                Input::make('student.source_lead_id')
                    ->title(tkey('students.fields.source_lead'))
                    ->disabled(),
                Input::make('student.source_lead_number')
                    ->title(tkey('crm.leads.fields.lead_number'))
                    ->disabled(),
                Input::make('student.source_lead_status')
                    ->title(tkey('crm.leads.fields.status'))
                    ->disabled(),
                Input::make('student.source_lead_source')
                    ->title(tkey('crm.leads.fields.source'))
                    ->disabled(),
                Input::make('student.source_lead_manager')
                    ->title(tkey('crm.leads.fields.manager'))
                    ->disabled(),
                Input::make('student.source_lead_created_at')
                    ->title(tkey('crm.leads.fields.created_at'))
                    ->disabled(),
                Input::make('student.source_lead_converted_at')
                    ->title(tkey('crm.leads.fields.converted_at'))
                    ->disabled(),
            ])
                ->title(tkey('students.sections.crm_source'))
                ->canSee($this->canViewCrmSource()),

            Layout::rows([
                Input::make('student.source_lead_utm_source')
                    ->title(tkey('crm.leads.fields.utm_source'))
                    ->disabled(),
                Input::make('student.source_lead_utm_medium')
                    ->title(tkey('crm.leads.fields.utm_medium'))
                    ->disabled(),
                Input::make('student.source_lead_utm_campaign')
                    ->title(tkey('crm.leads.fields.utm_campaign'))
                    ->disabled(),
                Input::make('student.source_lead_landing_page')
                    ->title(tkey('crm.leads.fields.landing_page'))
                    ->disabled(),
                Input::make('student.source_lead_form_page')
                    ->title(tkey('crm.leads.fields.form_page'))
                    ->disabled(),
                Input::make('student.source_lead_form_name')
                    ->title(tkey('crm.leads.fields.form_name'))
                    ->disabled(),
            ])
                ->title(tkey('crm.leads.sections.marketing_data'))
                ->canSee($this->canViewMarketing()),

            Layout::table('student.enrollments', [
                TD::make('enrollment_number', tkey('students.enrollments.fields.enrollment_number'))
                    ->render(fn (StudentEnrollment $enrollment): string => (string) Link::make($enrollment->enrollment_number ?: (string) $enrollment->id)
                        ->route('platform.students.enrollments.edit', $enrollment)),
                TD::make('course', tkey('students.enrollments.fields.course'))
                    ->render(fn (StudentEnrollment $enrollment): string => $enrollment->trainingProgram?->displayTitle() ?? '-'),
                TD::make('branch', tkey('students.enrollments.fields.branch'))
                    ->render(fn (StudentEnrollment $enrollment): string => $enrollment->branch?->displayName() ?? '-'),
                TD::make('group', tkey('students.enrollments.fields.training_group'))
                    ->render(fn (StudentEnrollment $enrollment): string => $enrollment->trainingGroup?->displayName() ?? tkey('students.warnings.missing_group')),
                TD::make('status', tkey('students.enrollments.fields.status'))
                    ->render(fn (StudentEnrollment $enrollment): string => tkey('students.enrollments.statuses.'.$enrollment->status->value)),
                TD::make('start_date', tkey('students.enrollments.fields.start_date'))
                    ->render(fn (StudentEnrollment $enrollment): string => $enrollment->start_date?->toDateString() ?? '-'),
            ])->title(tkey('students.sections.enrollments')),

            Layout::table('student.tasks', [
                TD::make('due_at', tkey('students.tasks.fields.due_at'))
                    ->render(fn (StudentTask $task): string => $this->taskDueLabel($task)),
                TD::make('title', tkey('students.tasks.fields.title'))
                    ->render(fn (StudentTask $task): string => $task->display_title),
                TD::make('assigned_to', tkey('students.tasks.fields.assigned_to'))
                    ->render(fn (StudentTask $task): string => $task->assignedTo?->name ?? '-'),
                TD::make('priority', tkey('students.tasks.fields.priority'))
                    ->render(fn (StudentTask $task): string => tkey('students.tasks.priorities.'.$task->priority)),
                TD::make('status', tkey('students.tasks.fields.status'))
                    ->render(fn (StudentTask $task): string => tkey('students.tasks.statuses.'.$task->status)),
                TD::make('actions', tkey('crm.leads.columns.actions'))
                    ->alignRight()
                    ->render(fn (StudentTask $task): string => collect([
                        (string) Button::make(tkey('students.actions.complete_task'))
                            ->icon('bs.check2')
                            ->method('completeTask')
                            ->parameters(['task' => $task->id])
                            ->canSee(! in_array($task->status, ['done', 'cancelled'], true)),
                        (string) Button::make(tkey('students.actions.cancel_task'))
                            ->icon('bs.x-circle')
                            ->method('cancelTask')
                            ->parameters(['task' => $task->id])
                            ->canSee(! in_array($task->status, ['done', 'cancelled'], true)),
                    ])->join(' ')),
            ])->title(tkey('students.sections.tasks')),

            Layout::table('student.activities', [
                TD::make('created_at', tkey('students.fields.created_at'))
                    ->render(fn (StudentActivity $activity): string => $activity->created_at?->format('Y-m-d H:i') ?? '-'),
                TD::make('type', tkey('crm.activities.fields.type'))
                    ->render(fn (StudentActivity $activity): string => tkey('students.activities.types.'.$activity->type)),
                TD::make('user', tkey('crm.leads.columns.user'))
                    ->render(fn (StudentActivity $activity): string => $activity->user?->name ?? tkey('common.system')),
                TD::make('body', tkey('crm.activities.fields.body'))
                    ->render(fn (StudentActivity $activity): string => $activity->body ?? $activity->title ?? '-'),
                TD::make('change', tkey('crm.activities.fields.change'))
                    ->render(fn (StudentActivity $activity): string => collect([$activity->old_value, $activity->new_value])->filter()->join(' -> ') ?: '-'),
            ])->title(tkey('students.sections.activities')),

            Layout::rows([
                Input::make('student.uuid')
                    ->title(tkey('students.fields.uuid'))
                    ->disabled(),
                Input::make('student.created_by_name')
                    ->title(tkey('students.fields.created_by'))
                    ->disabled(),
                Input::make('student.updated_by_name')
                    ->title(tkey('students.fields.updated_by'))
                    ->disabled(),
                Input::make('student.created_at')
                    ->title(tkey('students.fields.created_at'))
                    ->disabled(),
                Input::make('student.updated_at')
                    ->title(tkey('students.fields.updated_at'))
                    ->disabled(),
            ])->title(tkey('students.sections.system_data')),

            Layout::modal('changeStatusModal', Layout::rows([
                Select::make('status')
                    ->title(tkey('students.fields.status'))
                    ->options($this->studentStatusOptions())
                    ->required(),
            ]))
                ->title(tkey('students.actions.change_status'))
                ->method('changeStatus')
                ->applyButton(tkey('students.actions.change_status'))
                ->canSee($this->student?->exists ?? false),

            Layout::modal('assignManagerModal', Layout::rows([
                Select::make('manager_id')
                    ->title(tkey('students.fields.manager'))
                    ->options($this->managers)
                    ->empty(tkey('students.empty.no_manager')),
            ]))
                ->title(tkey('students.actions.assign_manager'))
                ->method('assignManager')
                ->applyButton(tkey('students.actions.assign_manager'))
                ->canSee($this->student?->exists ?? false),

            Layout::modal('addNoteModal', Layout::rows([
                TextArea::make('body')
                    ->title(tkey('students.fields.comment'))
                    ->rows(4)
                    ->required(),
            ]))
                ->title(tkey('students.actions.add_note'))
                ->method('addNote')
                ->applyButton(tkey('students.actions.add_note'))
                ->canSee($this->student?->exists ?? false),

            Layout::modal('createTaskModal', Layout::rows([
                Input::make('task.title_translations.ru')
                    ->title(tkey('students.tasks.fields.title'))
                    ->required(),
                TextArea::make('task.description_translations.ru')
                    ->title(tkey('students.tasks.fields.description'))
                    ->rows(3),
                Select::make('task.enrollment_id')
                    ->title(tkey('students.tasks.fields.enrollment'))
                    ->options($this->enrollmentOptions())
                    ->empty(tkey('students.empty.no_enrollment')),
                Select::make('task.assigned_to_id')
                    ->title(tkey('students.tasks.fields.assigned_to'))
                    ->options($this->managers)
                    ->empty(tkey('students.empty.no_manager')),
                Select::make('task.priority')
                    ->title(tkey('students.tasks.fields.priority'))
                    ->options($this->taskPriorityOptions())
                    ->value('normal')
                    ->required(),
                Input::make('task.due_at')
                    ->title(tkey('students.tasks.fields.due_at'))
                    ->type('datetime-local'),
            ]))
                ->title(tkey('students.actions.create_task'))
                ->method('createTask')
                ->applyButton(tkey('students.actions.create_task'))
                ->canSee($this->student?->exists ?? false),

            Layout::modal('addEnrollmentModal', Layout::rows([
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
                Select::make('enrollment.status')
                    ->title(tkey('students.enrollments.fields.status'))
                    ->options($this->enrollmentStatusOptions()),
                Input::make('enrollment.start_date')
                    ->title(tkey('students.enrollments.fields.start_date'))
                    ->type('date'),
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
                Input::make('enrollment.price')
                    ->title(tkey('students.enrollments.fields.price'))
                    ->type('number')
                    ->step('0.01'),
                Select::make('enrollment.payment_status')
                    ->title(tkey('students.enrollments.fields.payment_status'))
                    ->options($this->paymentStatusOptions())
                    ->empty(tkey('students.filters.no_segment')),
            ]))
                ->title(tkey('students.actions.add_enrollment'))
                ->method('addEnrollment')
                ->applyButton(tkey('students.actions.add_enrollment'))
                ->canSee($this->student?->exists ?? false),
        ];
    }

    public function save(StudentRequest $request, CreateStudentAction $createStudent, UpdateStudentAction $updateStudent): RedirectResponse
    {
        $student = $this->persistStudent($request, $createStudent, $updateStudent);

        return redirect()->route('platform.students.edit', $student);
    }

    public function saveAndReturn(StudentRequest $request, CreateStudentAction $createStudent, UpdateStudentAction $updateStudent): RedirectResponse
    {
        $this->persistStudent($request, $createStudent, $updateStudent);

        return redirect()->route('platform.students');
    }

    public function changeStatus(Student $student, ChangeStudentStatusRequest $request, ChangeStudentStatusAction $changeStatus): RedirectResponse
    {
        $changeStatus->handle($student, $request->targetStatus(), $request->user(), $request->boolean('override_status_transition'));

        Toast::info(tkey('students.messages.status_changed'));

        return redirect()->route('platform.students.edit', $student);
    }

    public function assignManager(Student $student, AssignStudentManagerRequest $request, AssignStudentManagerAction $assignManager): RedirectResponse
    {
        $assignManager->handle($student, $request->managerId(), $request->user(), $request->boolean('assign_open_tasks', true));

        Toast::info(tkey('students.messages.manager_assigned'));

        return redirect()->route('platform.students.edit', $student);
    }

    public function addNote(Student $student, AddStudentNoteRequest $request, AddStudentNoteAction $addNote): RedirectResponse
    {
        $addNote->handle($student, $request->validated('body'), $request->user());

        Toast::info(tkey('students.messages.note_added'));

        return redirect()->route('platform.students.edit', $student);
    }

    public function createTask(Student $student, StoreStudentTaskRequest $request, CreateStudentTaskAction $createTask): RedirectResponse
    {
        $payload = $request->taskData();
        $enrollment = filled($payload['enrollment_id'] ?? null)
            ? $student->enrollments()->findOrFail($payload['enrollment_id'])
            : null;

        $createTask->handle(
            $student,
            $payload['title_translations'],
            $request->user(),
            filled($payload['due_at'] ?? null) ? Carbon::parse($payload['due_at']) : null,
            $payload['priority'],
            $payload['description_translations'] ?? null,
            $payload['assigned_to_id'] ?? null,
            $enrollment,
        );

        Toast::info(tkey('students.messages.task_created'));

        return redirect()->route('platform.students.edit', $student);
    }

    public function completeTask(Student $student, CompleteStudentTaskRequest $request, CompleteStudentTaskAction $completeTask): RedirectResponse
    {
        $task = $student->tasks()->findOrFail((int) $request->input('task'));

        $completeTask->handle($task, $request->user());

        Toast::info(tkey('students.messages.task_completed'));

        return redirect()->route('platform.students.edit', $student);
    }

    public function cancelTask(Student $student, CancelStudentTaskRequest $request, CancelStudentTaskAction $cancelTask): RedirectResponse
    {
        $task = $student->tasks()->findOrFail((int) $request->input('task'));

        $cancelTask->handle($task, $request->user());

        Toast::info(tkey('students.messages.task_cancelled'));

        return redirect()->route('platform.students.edit', $student);
    }

    public function createOnboardingTasks(Student $student, CreatePortalAccessRequest $request, CreateStudentOnboardingTasksAction $createTasks): RedirectResponse
    {
        $createTasks->handle($student, $request->user(), $student->current_enrollment);

        Toast::info(tkey('students.messages.onboarding_tasks_created'));

        return redirect()->route('platform.students.edit', $student);
    }

    public function addEnrollment(Student $student, StoreStudentEnrollmentRequest $request, CreateStudentEnrollmentAction $createEnrollment): RedirectResponse
    {
        $createEnrollment->handle($student, $request->enrollmentData(), $request->user(), $request->boolean('create_onboarding_tasks'));

        Toast::info(tkey('students.messages.enrollment_created'));

        return redirect()->route('platform.students.edit', $student);
    }

    public function createPortalAccess(Student $student, CreatePortalAccessRequest $request, CreatePortalAccessPlaceholderAction $createPortalAccess): RedirectResponse
    {
        $createPortalAccess->handle($student, $request->user());

        Toast::info(tkey('students.messages.portal_access_created'));

        return redirect()->route('platform.students.edit', $student);
    }

    public function archive(Student $student, ArchiveStudentRequest $request, ArchiveStudentAction $archiveStudent): RedirectResponse
    {
        $archiveStudent->handle($student, $request->user(), $request->boolean('override_active_enrollment'));

        Toast::info(tkey('students.messages.archived'));

        return redirect()->route('platform.students.edit', $student);
    }

    private function persistStudent(StudentRequest $request, CreateStudentAction $createStudent, UpdateStudentAction $updateStudent): Student
    {
        $routeStudent = $request->routeStudent();
        $isNew = $routeStudent === null;

        $student = $isNew
            ? $createStudent->handle($request->studentData(), $request->user(), $request->boolean('create_onboarding_tasks'))
            : $updateStudent->handle($routeStudent, $request->studentData(), $request->user(), $request->boolean('override_locked_student'));

        Toast::info($isNew ? tkey('students.messages.created') : tkey('students.messages.updated'));

        return $student;
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
    private function taskPriorityOptions(): array
    {
        return collect(StudentTaskPriority::values())
            ->mapWithKeys(fn (string $priority): array => [$priority => tkey('students.tasks.priorities.'.$priority)])
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private function enrollmentOptions(): array
    {
        return collect($this->student?->enrollments ?? [])
            ->mapWithKeys(fn (StudentEnrollment $enrollment): array => [$enrollment->id => $enrollment->display_name])
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
        return collect(CourseFormat::values())
            ->mapWithKeys(fn (string $format): array => [$format => tkey('students.training_formats.'.$format)])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    private function gearboxOptions(): array
    {
        return collect(GearboxType::values())
            ->mapWithKeys(fn (string $type): array => [$type => tkey('students.gearbox.'.$type)])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    private function paymentStatusOptions(): array
    {
        return collect(EnrollmentPaymentStatus::values())
            ->mapWithKeys(fn (string $status): array => [$status => tkey('students.payment_statuses.'.$status)])
            ->all();
    }

    private function taskDueLabel(StudentTask $task): string
    {
        $value = $task->due_at?->format('Y-m-d H:i') ?? '-';

        return $task->is_overdue ? tkey('students.tasks.labels.overdue_value', ['value' => $value]) : $value;
    }

    private function summaryValue(?array $summary): string
    {
        if ($summary === null) {
            return '-';
        }

        return json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?: '-';
    }

    private function canViewCrmSource(): bool
    {
        return request()->user()?->hasAccess('students.view_crm_source') ?? false;
    }

    private function canViewMarketing(): bool
    {
        return request()->user()?->hasAnyAccess(['students.view_marketing', 'crm.leads.view_marketing']) ?? false;
    }

    private function hasStudentAccess(string $permission): bool
    {
        return request()->user()?->hasAnyAccess([$permission, 'platform.crm.students']) ?? false;
    }
}
