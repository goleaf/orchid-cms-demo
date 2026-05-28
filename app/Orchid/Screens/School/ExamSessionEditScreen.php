<?php

namespace App\Orchid\Screens\School;

use App\Actions\AddStudentToExamSessionAction;
use App\Actions\CancelExamSessionAction;
use App\Actions\ChangeExamSessionStatusAction;
use App\Actions\CheckExamAdmissionAction;
use App\Actions\CreateExamSessionAction;
use App\Actions\UpdateExamSessionAction;
use App\Models\ExamActivity;
use App\Models\ExamAttempt;
use App\Models\ExamChecklistItem;
use App\Models\ExamParticipant;
use App\Models\ExamResult;
use App\Models\ExamSession;
use App\Models\StudentEnrollment;
use App\Orchid\Screens\School\Concerns\InteractsWithExamScreens;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class ExamSessionEditScreen extends Screen
{
    use InteractsWithExamScreens;

    public ?ExamSession $session = null;

    /**
     * @var array<int, string>
     */
    private array $types = [];

    /**
     * @var array<int, string>
     */
    private array $statuses = [];

    /**
     * @var array<int, string>
     */
    private array $branches = [];

    /**
     * @var array<int, string>
     */
    private array $groups = [];

    /**
     * @var array<int, string>
     */
    private array $examiners = [];

    /**
     * @var array<int, string>
     */
    private array $vehicles = [];

    /**
     * @var array<int, string>
     */
    private array $students = [];

    /**
     * @var array<int, string>
     */
    private array $enrollments = [];

    public function query(?ExamSession $examSession = null): iterable
    {
        $this->types = $this->examTypeOptions();
        $this->statuses = $this->examStatusOptions();
        $this->branches = $this->branchOptions();
        $this->groups = $this->groupOptions();
        $this->examiners = $this->examinerOptions();
        $this->vehicles = $this->vehicleOptions();
        $this->students = $this->studentOptions();
        $this->enrollments = $this->enrollmentOptions();

        $session = $examSession?->exists
            ? $examSession->loadMissing([
                'typeRecord:id,code,name,name_translations',
                'statusRecord:id,code,name,name_translations',
                'branch:id,name,name_translations,city,city_translations',
                'group:id,group_number,name,name_translations',
                'groupAlias:id,group_number,name,name_translations',
                'examiner:id,name',
                'vehicle:id,registration_number,make,model',
                'participants.student:id,first_name,last_name,full_name,student_number',
                'participants.enrollment:id,enrollment_number,student_profile_id,training_program_id',
                'checklistItems.student:id,first_name,last_name,full_name,student_number',
                'attempts.statusRecord:id,code,name,name_translations',
                'attempts.student:id,first_name,last_name,full_name,student_number',
                'attempts.enrollment:id,enrollment_number,student_profile_id,training_program_id',
                'results.resultStatus:id,code,name,name_translations',
                'results.attempt.student:id,first_name,last_name,full_name,student_number',
                'activities.user:id,name',
            ])
            : new ExamSession([
                'type_id' => array_key_first($this->types),
                'status_id' => array_key_first($this->statuses),
                'capacity' => 8,
                'scheduled_at' => now()->addDay()->setTime(9, 0),
                'starts_at' => now()->addDay()->setTime(9, 0),
            ]);
        $this->session = $session;

        return [
            'sessionModel' => $session,
            'id' => $session->id,
            'exam_session_id' => $session->id,
            'exam_number' => $session->exam_number,
            'type_id' => $session->type_id,
            'status_id' => $session->status_id,
            'branch_id' => $session->branch_id,
            'group_id' => $session->group_id ?? $session->training_group_id,
            'scheduled_at' => $this->fieldDateTime($session->scheduled_at ?? $session->starts_at),
            'location' => $session->location,
            'examiner_id' => $session->examiner_id,
            'vehicle_id' => $session->vehicle_id,
            'classroom_id' => $session->classroom_id,
            'capacity' => $session->capacity ?? 8,
            'notes' => $session->notes,
            'internal_notes' => $session->internal_notes,
            'participants' => $session->participants ?? collect(),
            'checklistItems' => $session->checklistItems ?? collect(),
            'attempts' => $session->attempts ?? collect(),
            'results' => $session->results ?? collect(),
            'activities' => $session->activities ?? collect(),
        ];
    }

    public function name(): ?string
    {
        return $this->session?->exists
            ? tkey('exams.sessions.edit_title')
            : tkey('exams.sessions.create_title');
    }

    public function description(): ?string
    {
        return tkey('exams.sessions.title');
    }

    public function permission(): iterable
    {
        return ['exams.sessions.view'];
    }

    public function commandBar(): iterable
    {
        return [
            Link::make(tkey('common.actions.back'))
                ->icon('bs.arrow-left')
                ->route('platform.exams.sessions'),

            Button::make(tkey('exams.actions.save'))
                ->icon('bs.check-lg')
                ->method($this->session?->exists ? 'updateSession' : 'createSession')
                ->canSee($this->session?->exists
                    ? (request()->user()?->hasAccess('exams.sessions.update') ?? false)
                    : (request()->user()?->hasAccess('exams.sessions.create') ?? false)),

            ModalToggle::make(tkey('exams.actions.change_status'))
                ->icon('bs.arrow-repeat')
                ->modal('changeStatusModal')
                ->method('changeStatus')
                ->canSee(($this->session?->exists ?? false) && (request()->user()?->hasAccess('exams.sessions.update') ?? false)),

            ModalToggle::make(tkey('exams.actions.add_student'))
                ->icon('bs.person-plus')
                ->modal('addStudentModal')
                ->method('addStudent')
                ->canSee(($this->session?->exists ?? false) && (request()->user()?->hasAccess('exams.sessions.update') ?? false)),

            Button::make(tkey('exams.actions.check_admissions'))
                ->icon('bs.ui-checks')
                ->method('checkAdmissions')
                ->canSee(($this->session?->exists ?? false) && (request()->user()?->hasAccess('exams.admissions.check') ?? false)),

            Button::make(tkey('exams.actions.cancel'))
                ->icon('bs.x-circle')
                ->method('cancelSession')
                ->confirm(tkey('exams.messages.cancel_confirm'))
                ->canSee(($this->session?->exists ?? false) && (request()->user()?->hasAccess('exams.sessions.cancel') ?? false)),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::rows([
                Input::make('exam_number')
                    ->title(tkey('exams.fields.exam_number'))
                    ->disabled(),
                Input::make('overview_status')
                    ->title(tkey('exams.fields.status'))
                    ->value($this->session?->exists ? $this->sessionStatusLabel($this->session) : $this->dash())
                    ->disabled(),
                Input::make('overview_type')
                    ->title(tkey('exams.fields.type'))
                    ->value($this->session?->exists ? $this->sessionTypeLabel($this->session) : $this->dash())
                    ->disabled(),
                Input::make('overview_participants')
                    ->title(tkey('exams.fields.participants_count'))
                    ->value((string) ($this->session?->participants?->count() ?? 0))
                    ->disabled(),
            ])->title(tkey('exams.sections.overview')),

            Layout::rows([
                Input::make('id')->type('hidden'),
                Select::make('type_id')
                    ->title(tkey('exams.fields.type'))
                    ->options($this->types)
                    ->required(),
                Select::make('status_id')
                    ->title(tkey('exams.fields.status'))
                    ->options($this->statuses)
                    ->required(),
                Select::make('branch_id')
                    ->title(tkey('exams.fields.branch'))
                    ->empty(tkey('exams.filters.no_branch'), '')
                    ->options($this->branches),
                Select::make('group_id')
                    ->title(tkey('exams.fields.group'))
                    ->empty(tkey('exams.filters.no_group'), '')
                    ->options($this->groups),
                Input::make('scheduled_at')
                    ->type('datetime-local')
                    ->title(tkey('exams.fields.scheduled_at'))
                    ->required(),
                Input::make('location')
                    ->title(tkey('exams.fields.location'))
                    ->maxlength(255),
                Select::make('examiner_id')
                    ->title(tkey('exams.fields.examiner'))
                    ->empty(tkey('exams.filters.no_examiner'), '')
                    ->options($this->examiners),
                Select::make('vehicle_id')
                    ->title(tkey('exams.fields.vehicle'))
                    ->empty(tkey('exams.filters.no_vehicle'), '')
                    ->options($this->vehicles),
                Input::make('classroom_id')
                    ->type('number')
                    ->title(tkey('exams.fields.classroom')),
                Input::make('capacity')
                    ->type('number')
                    ->title(tkey('exams.fields.capacity'))
                    ->required(),
                TextArea::make('notes')
                    ->title(tkey('exams.fields.notes'))
                    ->rows(3),
            ])->title(tkey('exams.sections.main_info')),

            Layout::table('participants', [
                TD::make('student_id', tkey('exams.fields.student'))
                    ->render(fn (ExamParticipant $participant): string => $participant->student?->display_name ?? $this->dash()),
                TD::make('enrollment_id', tkey('exams.fields.enrollment'))
                    ->render(fn (ExamParticipant $participant): string => $this->enrollmentLabel($participant->enrollment)),
                TD::make('status', tkey('exams.fields.status'))
                    ->render(fn (ExamParticipant $participant): string => $participant->displayStatus()),
                TD::make('admitted', tkey('exams.fields.admitted'))
                    ->render(fn (ExamParticipant $participant): string => $this->boolLabel($participant->admitted)),
                TD::make('registered_at', tkey('exams.fields.registered_at'))
                    ->render(fn (ExamParticipant $participant): string => $this->dateTime($participant->registered_at)),
                TD::make('block_reason', tkey('exams.fields.block_reason'))
                    ->render(fn (ExamParticipant $participant): string => $participant->block_reason ?? $this->dash()),
            ])->title(tkey('exams.sections.participants')),

            Layout::table('checklistItems', [
                TD::make('key', tkey('exams.fields.checklist'))
                    ->render(fn (ExamChecklistItem $item): string => $this->checklistTitle($item)),
                TD::make('student_id', tkey('exams.fields.student'))
                    ->render(fn (ExamChecklistItem $item): string => $item->student?->display_name ?? $this->dash()),
                TD::make('status', tkey('exams.fields.status'))
                    ->render(fn (ExamChecklistItem $item): string => tkey('exams.checklist.statuses.'.$item->status)),
                TD::make('required', tkey('exams.fields.required'))
                    ->render(fn (ExamChecklistItem $item): string => $this->boolLabel($item->required)),
            ])->title(tkey('exams.sections.admission_checklist')),

            Layout::table('attempts', [
                TD::make('attempt_number', tkey('exams.fields.exam_number'))
                    ->render(fn (ExamAttempt $attempt): string => (string) Link::make($attempt->attempt_number ? (string) $attempt->attempt_number : $this->dash())
                        ->route('platform.exams.attempts.edit', $attempt)),
                TD::make('student_id', tkey('exams.fields.student'))
                    ->render(fn (ExamAttempt $attempt): string => $attempt->student?->display_name ?? $this->dash()),
                TD::make('status_id', tkey('exams.fields.status'))
                    ->render(fn (ExamAttempt $attempt): string => $this->attemptStatusLabel($attempt)),
                TD::make('attempt_no', tkey('exams.fields.attempt_no'))
                    ->alignCenter()
                    ->render(fn (ExamAttempt $attempt): string => (string) ($attempt->attempt_no ?? $attempt->attempt_number ?? $this->dash())),
                TD::make('passed', tkey('exams.fields.passed'))
                    ->render(fn (ExamAttempt $attempt): string => $this->boolLabel($attempt->passed)),
            ])->title(tkey('exams.sections.attempts')),

            Layout::table('results', [
                TD::make('attempt_id', tkey('exams.fields.attempt_no'))
                    ->render(fn (ExamResult $result): string => (string) ($result->attempt?->attempt_no ?? $result->attempt?->attempt_number ?? $this->dash())),
                TD::make('student', tkey('exams.fields.student'))
                    ->render(fn (ExamResult $result): string => $result->attempt?->student?->display_name ?? $this->dash()),
                TD::make('result_status_id', tkey('exams.fields.result_status'))
                    ->render(fn (ExamResult $result): string => $this->resultStatusLabel($result)),
                TD::make('score', tkey('exams.fields.score'))
                    ->render(fn (ExamResult $result): string => trim((string) $result->score.'/'.$result->max_score, '/')),
                TD::make('passed', tkey('exams.fields.passed'))
                    ->render(fn (ExamResult $result): string => $this->boolLabel($result->passed)),
                TD::make('decided_at', tkey('exams.fields.decided_at'))
                    ->render(fn (ExamResult $result): string => $this->dateTime($result->decided_at)),
            ])->title(tkey('exams.sections.results')),

            Layout::table('activities', [
                TD::make('created_at', tkey('exams.fields.created_at'))
                    ->render(fn (ExamActivity $activity): string => $this->dateTime($activity->created_at)),
                TD::make('type', tkey('exams.fields.type'))
                    ->render(fn (ExamActivity $activity): string => $this->activityTypeLabel($activity)),
                TD::make('user_id', tkey('exams.fields.user'))
                    ->render(fn (ExamActivity $activity): string => $activity->user?->name ?? $this->dash()),
                TD::make('new_value', tkey('exams.fields.new_value'))
                    ->render(fn (ExamActivity $activity): string => $activity->new_value ?? $this->dash()),
            ])->title(tkey('exams.sections.activities')),

            Layout::rows([
                TextArea::make('internal_notes')
                    ->title(tkey('exams.fields.internal_notes'))
                    ->rows(3),
                Input::make('system_created_at')
                    ->title(tkey('exams.fields.created_at'))
                    ->value($this->dateTime($this->session?->created_at))
                    ->disabled(),
                Input::make('system_updated_at')
                    ->title(tkey('exams.fields.updated_at'))
                    ->value($this->dateTime($this->session?->updated_at))
                    ->disabled(),
            ])->title(tkey('exams.sections.system')),

            Layout::modal('changeStatusModal', [
                Layout::rows([
                    Input::make('exam_session_id')->type('hidden')->value($this->session?->id),
                    Select::make('status_id')
                        ->title(tkey('exams.fields.status'))
                        ->options($this->statuses)
                        ->required(),
                    TextArea::make('reason')
                        ->title(tkey('exams.fields.reason'))
                        ->rows(3),
                ]),
            ])
                ->title(tkey('exams.actions.change_status'))
                ->applyButton(tkey('exams.actions.save')),

            Layout::modal('addStudentModal', [
                Layout::rows([
                    Input::make('exam_session_id')->type('hidden')->value($this->session?->id),
                    Select::make('student_id')
                        ->title(tkey('exams.fields.student'))
                        ->empty(tkey('exams.filters.choose_student'), '')
                        ->options($this->students),
                    Select::make('enrollment_id')
                        ->title(tkey('exams.fields.enrollment'))
                        ->options($this->enrollments)
                        ->required(),
                    Select::make('admitted')
                        ->title(tkey('exams.fields.admitted'))
                        ->options($this->yesNoOptions())
                        ->value('1'),
                    Select::make('allow_overbooking')
                        ->title(tkey('exams.fields.allow_overbooking'))
                        ->options($this->yesNoOptions())
                        ->value('0'),
                    TextArea::make('block_reason')
                        ->title(tkey('exams.fields.block_reason'))
                        ->rows(3),
                ]),
            ])
                ->title(tkey('exams.actions.add_student'))
                ->applyButton(tkey('exams.actions.add_student')),
        ];
    }

    public function createSession(Request $request, CreateExamSessionAction $createSession): RedirectResponse
    {
        abort_unless($request->user()?->hasAccess('exams.sessions.create'), 403);

        $session = $createSession->handle($this->validatedSessionData($request, true), $request->user());

        Toast::info(tkey('exams.messages.session_saved'));

        return redirect()->route('platform.exams.sessions.edit', $session);
    }

    public function updateSession(Request $request, UpdateExamSessionAction $updateSession): RedirectResponse
    {
        abort_unless($request->user()?->hasAccess('exams.sessions.update'), 403);

        $session = ExamSession::query()->findOrFail($request->integer('id'));
        $updateSession->handle($session, $this->validatedSessionData($request, false), $request->user());

        Toast::info(tkey('exams.messages.session_saved'));

        return redirect()->route('platform.exams.sessions.edit', $session);
    }

    public function changeStatus(Request $request, ChangeExamSessionStatusAction $changeStatus): RedirectResponse
    {
        abort_unless($request->user()?->hasAccess('exams.sessions.update'), 403);

        $data = $request->validate([
            'exam_session_id' => ['required', 'integer', Rule::exists(ExamSession::class, 'id')],
            'status_id' => ['required', 'integer', Rule::exists(\App\Models\ExamStatus::class, 'id')],
        ], $this->validationMessages());

        $session = ExamSession::query()->findOrFail($data['exam_session_id']);
        $changeStatus->handle($session, (int) $data['status_id'], $request->user(), true);

        Toast::info(tkey('exams.messages.status_changed'));

        return redirect()->route('platform.exams.sessions.edit', $session);
    }

    public function addStudent(Request $request, AddStudentToExamSessionAction $addStudent): RedirectResponse
    {
        abort_unless($request->user()?->hasAccess('exams.sessions.update'), 403);

        $data = $request->validate([
            'exam_session_id' => ['required', 'integer', Rule::exists(ExamSession::class, 'id')],
            'student_id' => ['nullable', 'integer', Rule::exists(\App\Models\Student::class, 'id')],
            'enrollment_id' => ['required', 'integer', Rule::exists(StudentEnrollment::class, 'id')],
            'admitted' => ['nullable', 'boolean'],
            'allow_overbooking' => ['nullable', 'boolean'],
            'block_reason' => ['nullable', 'string', 'max:2000'],
        ], $this->validationMessages());

        $session = ExamSession::query()->findOrFail($data['exam_session_id']);
        $enrollment = StudentEnrollment::query()->findOrFail($data['enrollment_id']);
        $studentId = filled($data['student_id'] ?? null) ? (int) $data['student_id'] : (int) $enrollment->student_profile_id;

        $addStudent->handle(
            $session,
            $studentId,
            $enrollment,
            $request->user(),
            (bool) ($data['allow_overbooking'] ?? false),
            (bool) ($data['admitted'] ?? true),
            $data['block_reason'] ?? null,
        );

        Toast::info(tkey('exams.messages.student_added'));

        return redirect()->route('platform.exams.sessions.edit', $session);
    }

    public function checkAdmissions(Request $request, CheckExamAdmissionAction $checkAdmission): RedirectResponse
    {
        abort_unless($request->user()?->hasAccess('exams.admissions.check'), 403);

        $session = ExamSession::query()
            ->with(['participants.enrollment', 'typeRecord'])
            ->findOrFail($request->integer('id'));

        foreach ($session->participants as $participant) {
            if ($participant->enrollment !== null && $session->typeRecord !== null) {
                $checkAdmission->handle($participant->enrollment, $session->typeRecord, [], $request->user());
            }
        }

        Toast::info(tkey('exams.messages.admissions_checked'));

        return redirect()->route('platform.exams.sessions.edit', $session);
    }

    public function cancelSession(Request $request, CancelExamSessionAction $cancelSession): RedirectResponse
    {
        abort_unless($request->user()?->hasAccess('exams.sessions.cancel'), 403);

        $session = ExamSession::query()->findOrFail($request->integer('id'));
        $cancelSession->handle($session, $request->user());

        Toast::info(tkey('exams.messages.session_cancelled'));

        return redirect()->route('platform.exams.sessions.edit', $session);
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedSessionData(Request $request, bool $creating): array
    {
        $rules = [
            'id' => [$creating ? 'nullable' : 'required', 'integer', Rule::exists(ExamSession::class, 'id')],
            'type_id' => ['required', 'integer', Rule::exists(\App\Models\ExamType::class, 'id')],
            'status_id' => ['required', 'integer', Rule::exists(\App\Models\ExamStatus::class, 'id')],
            'branch_id' => ['nullable', 'integer', Rule::exists(\App\Models\Branch::class, 'id')],
            'group_id' => ['nullable', 'integer', Rule::exists(\App\Models\TrainingGroup::class, 'id')],
            'scheduled_at' => ['required', 'date'],
            'location' => ['nullable', 'string', 'max:255'],
            'examiner_id' => ['nullable', 'integer', Rule::exists(\App\Models\User::class, 'id')],
            'vehicle_id' => ['nullable', 'integer', Rule::exists(\App\Models\Vehicle::class, 'id')],
            'classroom_id' => ['nullable', 'integer', 'min:1'],
            'capacity' => ['required', 'integer', 'min:1', 'max:500'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'internal_notes' => ['nullable', 'string', 'max:2000'],
        ];

        $data = $request->validate($rules, $this->validationMessages());

        foreach (['id', 'type_id', 'status_id', 'branch_id', 'group_id', 'examiner_id', 'vehicle_id', 'classroom_id', 'capacity'] as $field) {
            if (array_key_exists($field, $data)) {
                $data[$field] = filled($data[$field]) ? (int) $data[$field] : null;
            }
        }

        return Arr::only($data, [
            'id',
            'type_id',
            'status_id',
            'branch_id',
            'group_id',
            'scheduled_at',
            'location',
            'examiner_id',
            'vehicle_id',
            'classroom_id',
            'capacity',
            'notes',
            'internal_notes',
        ]);
    }

    /**
     * @return array<string, string>
     */
    private function validationMessages(): array
    {
        return [
            'required' => tkey('exams.validation.required'),
            'integer' => tkey('exams.validation.integer'),
            'string' => tkey('exams.validation.string'),
            'date' => tkey('exams.validation.date'),
            'exists' => tkey('exams.validation.exists'),
            'min' => tkey('exams.validation.min'),
            'max' => tkey('exams.validation.max'),
            'boolean' => tkey('exams.validation.boolean'),
        ];
    }

    private function fieldDateTime(mixed $value): ?string
    {
        return $value === null ? null : \Illuminate\Support\Carbon::parse($value)->format('Y-m-d\TH:i');
    }
}
