<?php

declare(strict_types=1);

namespace App\Orchid\Screens\School;

use App\Actions\AddStudentToTrainingGroupAction;
use App\Actions\AddTrainingGroupNoteAction;
use App\Actions\ArchiveTrainingGroupAction;
use App\Actions\AssignLearningProgramToGroupAction;
use App\Actions\ChangeTrainingGroupStatusAction;
use App\Actions\CompleteTrainingGroupMembershipAction;
use App\Actions\CreateTrainingGroupAction;
use App\Actions\CreateTrainingGroupSchedulePatternAction;
use App\Actions\DeleteTrainingGroupSchedulePatternAction;
use App\Actions\HideTrainingGroupFromSiteAction;
use App\Actions\PublishTrainingGroupOnSiteAction;
use App\Actions\RecalculateTrainingGroupCapacityAction;
use App\Actions\RemoveStudentFromTrainingGroupAction;
use App\Actions\TransferStudentBetweenGroupsAction;
use App\Actions\UpdateTrainingGroupAction;
use App\Actions\WaitlistStudentForTrainingGroupAction;
use App\Enums\GroupStatus;
use App\Http\Requests\Education\AddTrainingGroupNoteRequest;
use App\Http\Requests\Education\ArchiveTrainingGroupRequest;
use App\Http\Requests\Education\AssignLearningProgramToGroupRequest;
use App\Http\Requests\Education\ChangeTrainingGroupStatusRequest;
use App\Http\Requests\Education\CompleteTrainingGroupMembershipRequest;
use App\Http\Requests\Education\DeleteTrainingGroupSchedulePatternRequest;
use App\Http\Requests\Education\HideTrainingGroupRequest;
use App\Http\Requests\Education\PublishTrainingGroupRequest;
use App\Http\Requests\Education\RemoveStudentFromTrainingGroupRequest;
use App\Http\Requests\Education\StoreTrainingGroupSchedulePatternRequest;
use App\Http\Requests\Education\TrainingGroupMembershipRequest;
use App\Http\Requests\Education\TransferStudentBetweenGroupsRequest;
use App\Http\Requests\Education\WaitlistStudentForTrainingGroupRequest;
use App\Http\Requests\TrainingGroupRequest;
use App\Models\Branch;
use App\Models\CourseCategory;
use App\Models\LearningProgram;
use App\Models\StudentEnrollment;
use App\Models\TrainingGroup;
use App\Models\TrainingGroupActivity;
use App\Models\TrainingGroupMembership;
use App\Models\TrainingGroupSchedulePattern;
use App\Models\TrainingGroupStatus;
use App\Models\TrainingProgram;
use App\Models\User;
use App\Orchid\Support\TranslatableFields;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\Switcher;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class TrainingGroupEditScreen extends Screen
{
    public ?TrainingGroup $group = null;

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
    private array $learningPrograms = [];

    /**
     * @var array<int, string>
     */
    private array $enrollments = [];

    /**
     * @var array<int, string>
     */
    private array $targetGroups = [];

    public function query(?TrainingGroup $group = null): iterable
    {
        $groupModel = $group?->exists
            ? $group->loadMissing([
                'branch:id,name,name_translations,city,city_translations',
                'course:id,title,title_translations,name_translations,license_category',
                'trainingProgram:id,title,title_translations,name_translations,license_category',
                'courseCategory:id,slug,code,name_translations',
                'statusRecord:id,code,name,name_translations,color,is_open_for_enrollment,is_archived,is_cancelled,is_success',
                'learningProgram:id,code,name_translations',
                'manager:id,name',
                'administrator:id,name',
                'teacher:id,name',
                'creator:id,name',
                'updater:id,name',
                'memberships.student:id,first_name,last_name,full_name,student_number',
                'memberships.enrollment:id,enrollment_number,student_profile_id,training_program_id,training_group_id',
                'schedulePatterns',
                'activities.user:id,name',
            ])
            : new TrainingGroup([
                'status' => GroupStatus::Planned,
                'capacity' => 12,
                'capacity_total' => 12,
                'capacity_reserved' => 0,
                'capacity_taken' => 0,
                'capacity_waitlist' => 0,
                'places_taken' => 0,
                'is_visible_on_site' => false,
                'is_featured' => false,
                'is_accepting_applications' => false,
                'timezone' => config('app.timezone'),
                'default_lesson_duration_minutes' => 90,
            ]);

        $this->group = $groupModel;
        $this->authorizeScreen($groupModel);
        $this->loadOptions($groupModel);

        return [
            'group' => $groupModel,
            'group.status' => $groupModel->status instanceof GroupStatus
                ? $groupModel->status->value
                : ($groupModel->status ?? GroupStatus::Planned->value),
            'group.meeting_days' => is_array($groupModel->meeting_days) ? implode(', ', $groupModel->meeting_days) : $groupModel->meeting_days,
            'group.meeting_time' => $groupModel->meeting_time?->format('H:i'),
            'group.end_time' => $groupModel->end_time?->format('H:i'),
            'group.capacity' => $groupModel->capacity_total ?? $groupModel->capacity,
            'group.places_taken' => $groupModel->capacity_taken ?? $groupModel->places_taken,
            'membership.training_group_id' => $groupModel->id,
            'training_group_id' => $groupModel->id,
            'group_id' => $groupModel->id,
            'pattern.training_group_id' => $groupModel->id,
            'name_translations' => $groupModel->getTranslations('name') ?: [],
            'description_translations' => $groupModel->getTranslations('description') ?: [],
            'public_description_translations' => $groupModel->getTranslations('public_description') ?: [],
            'schedule_summary_translations' => $groupModel->getTranslations('schedule_summary') ?: [],
            'memberships' => $groupModel->memberships ?? collect(),
            'schedulePatterns' => $groupModel->schedulePatterns ?? collect(),
            'activities' => $groupModel->activities?->sortByDesc('created_at')->values() ?? collect(),
        ];
    }

    public function name(): ?string
    {
        return $this->group?->exists
            ? tkey('education.groups.edit_title')
            : tkey('education.groups.create_title');
    }

    public function description(): ?string
    {
        return tkey('education.groups.title');
    }

    public function permission(): iterable
    {
        return ['education.groups.create', 'education.groups.update'];
    }

    public function commandBar(): iterable
    {
        return [
            Link::make(tkey('common.actions.back'))
                ->icon('bs.arrow-left')
                ->route('platform.education.groups'),

            Button::make(tkey('education.groups.actions.save'))
                ->icon('bs.check2-circle')
                ->method('save'),

            Button::make(tkey('education.groups.actions.save_and_return'))
                ->icon('bs.check2-all')
                ->method('saveAndReturn'),

            ModalToggle::make(tkey('education.groups.actions.change_status'))
                ->icon('bs.arrow-repeat')
                ->modal('changeStatusModal')
                ->canSee($this->group?->exists && $this->hasAccess('education.groups.change_status')),

            ModalToggle::make(tkey('education.groups.actions.add_student'))
                ->icon('bs.person-plus')
                ->modal('addStudentModal')
                ->canSee($this->group?->exists && $this->hasAccess('education.groups.manage_students')),

            ModalToggle::make(tkey('education.groups.actions.waitlist_student'))
                ->icon('bs.person-dash')
                ->modal('waitlistStudentModal')
                ->canSee($this->group?->exists && $this->hasAccess('education.groups.manage_students')),

            ModalToggle::make(tkey('education.groups.actions.create_schedule_pattern'))
                ->icon('bs.calendar-plus')
                ->modal('createSchedulePatternModal')
                ->canSee($this->group?->exists && $this->hasAccess('education.groups.manage_schedule_patterns')),

            ModalToggle::make(tkey('education.groups.actions.assign_learning_program'))
                ->icon('bs.journal-check')
                ->modal('assignLearningProgramModal')
                ->canSee($this->group?->exists && $this->hasAccess('education.groups.manage_learning_program')),

            Button::make(tkey('education.groups.actions.recalculate_capacity'))
                ->icon('bs.arrow-clockwise')
                ->method('recalculateCapacity')
                ->parameters(['group_id' => $this->group?->id])
                ->canSee($this->group?->exists && $this->hasAccess('education.groups.update')),

            Button::make(tkey('education.groups.actions.publish_on_site'))
                ->icon('bs.eye')
                ->method('publishOnSite')
                ->parameters(['group_id' => $this->group?->id])
                ->confirm(tkey('education.groups.messages.publish_confirm'))
                ->canSee($this->group?->exists && $this->hasAccess('education.groups.manage_public_visibility') && ! $this->group?->is_visible_on_site),

            Button::make(tkey('education.groups.actions.hide_from_site'))
                ->icon('bs.eye-slash')
                ->method('hideFromSite')
                ->parameters(['group_id' => $this->group?->id])
                ->confirm(tkey('education.groups.messages.hide_confirm'))
                ->canSee($this->group?->exists && $this->hasAccess('education.groups.manage_public_visibility') && $this->group?->is_visible_on_site),

            ModalToggle::make(tkey('education.groups.actions.add_note'))
                ->icon('bs.chat-left-text')
                ->modal('addNoteModal')
                ->canSee($this->group?->exists && $this->hasAccess('education.groups.update')),

            Button::make(tkey('education.groups.actions.archive'))
                ->icon('bs.archive')
                ->method('archive')
                ->parameters(['group_id' => $this->group?->id])
                ->confirm(tkey('education.groups.messages.archive_confirm'))
                ->canSee($this->group?->exists && $this->hasAccess('education.groups.archive')),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::rows([
                Input::make('group.id')->type('hidden'),
                Input::make('group.group_number')
                    ->title(tkey('education.groups.fields.group_number'))
                    ->disabled(),
                Input::make('group.code')
                    ->title(tkey('education.groups.fields.code'))
                    ->required(),
                Select::make('group.status_id')
                    ->title(tkey('education.groups.fields.status'))
                    ->options($this->statuses)
                    ->required(),
                Input::make('group.status')
                    ->type('hidden'),
                Select::make('group.course_id')
                    ->title(tkey('education.groups.fields.course'))
                    ->options($this->courses)
                    ->empty(tkey('education.groups.empty.no_course'), ''),
                Select::make('group.training_program_id')
                    ->title(tkey('education.groups.fields.course'))
                    ->options($this->courses)
                    ->empty(tkey('education.groups.empty.no_course'), ''),
                Select::make('group.course_category_id')
                    ->title(tkey('education.groups.fields.course_category'))
                    ->options($this->categories)
                    ->empty(tkey('education.groups.segments.all'), ''),
                Select::make('group.branch_id')
                    ->title(tkey('education.groups.fields.branch'))
                    ->options($this->branches)
                    ->empty(tkey('education.groups.empty.no_branch'), ''),
                Select::make('group.learning_program_id')
                    ->title(tkey('education.groups.fields.learning_program'))
                    ->options($this->learningPrograms)
                    ->empty(tkey('education.groups.empty.no_program'), ''),
                Select::make('group.manager_id')
                    ->title(tkey('education.groups.fields.manager'))
                    ->options($this->users)
                    ->empty(tkey('education.groups.segments.all'), ''),
                Select::make('group.administrator_id')
                    ->title(tkey('education.groups.fields.administrator'))
                    ->options($this->users)
                    ->empty(tkey('education.groups.segments.all'), ''),
                Select::make('group.teacher_id')
                    ->title(tkey('education.groups.fields.teacher'))
                    ->options($this->users)
                    ->empty(tkey('education.groups.segments.all'), ''),
            ])->title(tkey('education.groups.sections.main_information')),

            TranslatableFields::input('name', 'education.groups.fields.name', [
                'title_key' => 'education.groups.sections.translated_content',
                'maxlength' => 255,
                'required' => true,
            ]),

            TranslatableFields::textarea('description', 'education.groups.fields.description', [
                'title_key' => 'education.groups.sections.translated_content',
                'rows' => 3,
                'maxlength' => 2000,
            ]),

            TranslatableFields::textarea('public_description', 'education.groups.fields.public_description', [
                'title_key' => 'education.groups.sections.public_visibility',
                'rows' => 3,
                'maxlength' => 2000,
            ]),

            TranslatableFields::textarea('schedule_summary', 'education.groups.fields.schedule_summary', [
                'title_key' => 'education.groups.sections.schedule_patterns',
                'rows' => 2,
                'maxlength' => 1000,
            ]),

            Layout::columns([
                Layout::rows([
                    Input::make('group.start_date')
                        ->type('date')
                        ->title(tkey('education.groups.fields.start_date')),
                    Input::make('group.planned_end_date')
                        ->type('date')
                        ->title(tkey('education.groups.fields.planned_end_date')),
                    Input::make('group.actual_end_date')
                        ->type('date')
                        ->title(tkey('education.groups.fields.actual_end_date'))
                        ->disabled(),
                    Input::make('group.enrollment_closes_on')
                        ->type('date')
                        ->title(tkey('education.groups.fields.enrollment_closes_on')),
                ])->title(tkey('education.groups.sections.dates')),
                Layout::rows([
                    Input::make('group.capacity_total')
                        ->type('number')
                        ->min(1)
                        ->title(tkey('education.groups.fields.capacity_total'))
                        ->required(),
                    Input::make('group.capacity_reserved')
                        ->type('number')
                        ->min(0)
                        ->title(tkey('education.groups.fields.capacity_reserved')),
                    Input::make('group.capacity_taken')
                        ->type('number')
                        ->title(tkey('education.groups.fields.capacity_taken'))
                        ->disabled(),
                    Input::make('group.capacity_waitlist')
                        ->type('number')
                        ->title(tkey('education.groups.fields.capacity_waitlist'))
                        ->disabled(),
                    Input::make('group.available_places')
                        ->title(tkey('education.groups.fields.available_places'))
                        ->value((string) ($this->group?->available_places ?? 0))
                        ->disabled(),
                    Input::make('group.capacity_percent')
                        ->title(tkey('education.groups.fields.capacity_percent'))
                        ->value((string) ($this->group?->capacity_percent ?? 0))
                        ->disabled(),
                ])->title(tkey('education.groups.sections.capacity')),
            ]),

            Layout::rows([
                Switcher::make('group.is_visible_on_site')
                    ->sendTrueOrFalse()
                    ->title(tkey('education.groups.fields.is_visible_on_site')),
                Switcher::make('group.is_featured')
                    ->sendTrueOrFalse()
                    ->title(tkey('education.groups.fields.is_featured')),
                Switcher::make('group.is_accepting_applications')
                    ->sendTrueOrFalse()
                    ->title(tkey('education.groups.fields.is_accepting_applications')),
                Input::make('group.timezone')
                    ->title(tkey('education.groups.fields.timezone')),
                Input::make('group.default_lesson_duration_minutes')
                    ->type('number')
                    ->min(1)
                    ->title(tkey('education.groups.fields.default_lesson_duration_minutes')),
            ])->title(tkey('education.groups.sections.public_visibility')),

            Layout::table('memberships', [
                TD::make('student', tkey('education.groups.memberships.fields.student'))
                    ->render(fn (TrainingGroupMembership $membership): string => $membership->student?->display_name ?? '-'),
                TD::make('enrollment', tkey('education.groups.memberships.fields.enrollment'))
                    ->render(fn (TrainingGroupMembership $membership): string => $membership->enrollment?->display_name ?? '-'),
                TD::make('status', tkey('education.groups.memberships.fields.status'))
                    ->render(fn (TrainingGroupMembership $membership): string => tkey('education.groups.memberships.statuses.'.$membership->status)),
                TD::make('joined_at', tkey('education.groups.memberships.fields.joined_at'))
                    ->render(fn (TrainingGroupMembership $membership): string => $membership->joined_at?->format('Y-m-d H:i') ?? '-'),
                TD::make('left_at', tkey('education.groups.memberships.fields.left_at'))
                    ->render(fn (TrainingGroupMembership $membership): string => $membership->left_at?->format('Y-m-d H:i') ?? '-'),
                TD::make('actions', tkey('crm.leads.columns.actions'))
                    ->alignRight()
                    ->render(fn (TrainingGroupMembership $membership): DropDown => DropDown::make()
                        ->icon('bs.three-dots-vertical')
                        ->list([
                            Link::make(tkey('students.actions.open'))
                                ->icon('bs.person')
                                ->href($membership->student !== null ? route('platform.students.edit', $membership->student) : '#')
                                ->canSee($membership->student !== null),
                            Link::make(tkey('students.actions.edit_enrollment'))
                                ->icon('bs.card-list')
                                ->href($membership->enrollment !== null ? route('platform.students.enrollments.edit', $membership->enrollment) : '#')
                                ->canSee($membership->enrollment !== null),
                            ModalToggle::make(tkey('education.groups.actions.transfer_student'))
                                ->icon('bs.arrow-left-right')
                                ->modal('transferStudentModal')
                                ->method('transferStudent')
                                ->parameters(['membership_id' => $membership->id])
                                ->canSee($this->hasAccess('education.groups.manage_students') && $membership->is_active),
                            Button::make(tkey('education.groups.actions.remove_student'))
                                ->icon('bs.person-x')
                                ->method('removeStudent')
                                ->parameters(['membership_id' => $membership->id])
                                ->confirm(tkey('education.groups.messages.remove_confirm'))
                                ->canSee($this->hasAccess('education.groups.manage_students') && $membership->is_active),
                            Button::make(tkey('education.groups.actions.complete_membership'))
                                ->icon('bs.check2-circle')
                                ->method('completeMembership')
                                ->parameters(['membership_id' => $membership->id])
                                ->canSee($this->hasAccess('education.groups.manage_students') && $membership->is_active),
                        ])),
            ])->title(tkey('education.groups.sections.members')),

            Layout::table('schedulePatterns', [
                TD::make('type', tkey('education.schedule_patterns.fields.type'))
                    ->render(fn (TrainingGroupSchedulePattern $pattern): string => tkey('education.schedule_patterns.types.'.$pattern->type)),
                TD::make('day_of_week', tkey('education.schedule_patterns.fields.day_of_week'))
                    ->render(fn (TrainingGroupSchedulePattern $pattern): string => $pattern->display_day),
                TD::make('start_time', tkey('education.schedule_patterns.fields.start_time'))
                    ->render(fn (TrainingGroupSchedulePattern $pattern): string => $pattern->start_time?->format('H:i') ?? $pattern->starts_at?->format('H:i') ?? '-'),
                TD::make('end_time', tkey('education.schedule_patterns.fields.end_time'))
                    ->render(fn (TrainingGroupSchedulePattern $pattern): string => $pattern->end_time?->format('H:i') ?? $pattern->ends_at?->format('H:i') ?? '-'),
                TD::make('location', tkey('education.schedule_patterns.fields.location'))
                    ->render(fn (TrainingGroupSchedulePattern $pattern): string => $pattern->getTranslation('location') ?: '-'),
                TD::make('is_active', tkey('education.schedule_patterns.fields.is_active'))
                    ->render(fn (TrainingGroupSchedulePattern $pattern): string => $pattern->is_active ? tkey('common.status.yes') : tkey('common.status.no')),
                TD::make('actions', tkey('crm.leads.columns.actions'))
                    ->alignRight()
                    ->render(fn (TrainingGroupSchedulePattern $pattern): DropDown => DropDown::make()
                        ->icon('bs.three-dots-vertical')
                        ->list([
                            Button::make(tkey('education.groups.actions.delete_schedule_pattern'))
                                ->icon('bs.trash3')
                                ->method('deleteSchedulePattern')
                                ->parameters(['pattern_id' => $pattern->id])
                                ->confirm(tkey('education.groups.messages.delete_schedule_pattern_confirm'))
                                ->canSee($this->hasAccess('education.groups.manage_schedule_patterns')),
                        ])),
            ])->title(tkey('education.groups.sections.schedule_patterns')),

            Layout::table('activities', [
                TD::make('created_at', tkey('education.groups.fields.created_at'))
                    ->render(fn (TrainingGroupActivity $activity): string => $activity->created_at?->format('Y-m-d H:i') ?? '-'),
                TD::make('type', tkey('education.schedule_patterns.fields.type'))
                    ->render(fn (TrainingGroupActivity $activity): string => $activity->display_type),
                TD::make('user', tkey('education.groups.fields.updated_by'))
                    ->render(fn (TrainingGroupActivity $activity): string => $activity->user?->name ?? '-'),
                TD::make('body', tkey('education.groups.memberships.fields.notes'))
                    ->render(fn (TrainingGroupActivity $activity): string => $activity->body ?? $activity->title ?? '-'),
            ])->title(tkey('education.groups.sections.activities')),

            Layout::rows([
                TextArea::make('group.notes')
                    ->title(tkey('education.groups.fields.notes'))
                    ->rows(3),
                TextArea::make('group.internal_notes')
                    ->title(tkey('education.groups.fields.internal_notes'))
                    ->rows(3),
            ])->title(tkey('education.groups.sections.notes')),

            Layout::rows([
                Input::make('group.uuid')
                    ->title(tkey('education.groups.fields.uuid'))
                    ->disabled(),
                Input::make('created_by')
                    ->title(tkey('education.groups.fields.created_by'))
                    ->value($this->group?->creator?->name ?? '-')
                    ->disabled(),
                Input::make('updated_by')
                    ->title(tkey('education.groups.fields.updated_by'))
                    ->value($this->group?->updater?->name ?? '-')
                    ->disabled(),
                Input::make('created_at')
                    ->title(tkey('education.groups.fields.created_at'))
                    ->value($this->group?->created_at?->format('Y-m-d H:i') ?? '-')
                    ->disabled(),
                Input::make('updated_at')
                    ->title(tkey('education.groups.fields.updated_at'))
                    ->value($this->group?->updated_at?->format('Y-m-d H:i') ?? '-')
                    ->disabled(),
            ])->title(tkey('education.groups.sections.system_data')),

            ...$this->modals(),
        ];
    }

    public function save(
        TrainingGroupRequest $request,
        CreateTrainingGroupAction $createGroup,
        UpdateTrainingGroupAction $updateGroup,
    ): RedirectResponse {
        $group = $this->persist($request, $createGroup, $updateGroup);

        Toast::info($group->wasRecentlyCreated ? tkey('education.groups.messages.created') : tkey('education.groups.messages.updated'));

        return redirect()->route('platform.education.groups.edit', $group);
    }

    public function saveAndReturn(
        TrainingGroupRequest $request,
        CreateTrainingGroupAction $createGroup,
        UpdateTrainingGroupAction $updateGroup,
    ): RedirectResponse {
        $group = $this->persist($request, $createGroup, $updateGroup);

        Toast::info($group->wasRecentlyCreated ? tkey('education.groups.messages.created') : tkey('education.groups.messages.updated'));

        return redirect()->route('platform.education.groups');
    }

    public function changeStatus(
        ChangeTrainingGroupStatusRequest $request,
        ChangeTrainingGroupStatusAction $changeStatus,
    ): RedirectResponse {
        $group = TrainingGroup::query()->findOrFail($request->integer('group_id'));
        $changeStatus->handle($group, $request->integer('status_id'), $request->user(), $request->boolean('override_status_transition'), $request->input('comment'));

        Toast::info(tkey('education.groups.messages.status_changed'));

        return redirect()->route('platform.education.groups.edit', $group);
    }

    public function addStudent(TrainingGroupMembershipRequest $request, AddStudentToTrainingGroupAction $addStudent): RedirectResponse
    {
        $data = $request->membershipData();
        $enrollment = StudentEnrollment::query()->findOrFail((int) $data['enrollment_id']);

        $addStudent->handle($enrollment, (int) $data['training_group_id'], $request->user(), (bool) $data['allow_overbooking']);

        Toast::info(tkey('education.groups.messages.student_added'));

        return redirect()->route('platform.education.groups.edit', $data['training_group_id']);
    }

    public function waitlistStudent(
        WaitlistStudentForTrainingGroupRequest $request,
        WaitlistStudentForTrainingGroupAction $waitlistStudent,
    ): RedirectResponse {
        $group = TrainingGroup::query()->findOrFail($request->integer('training_group_id'));
        $enrollment = StudentEnrollment::query()->findOrFail($request->integer('enrollment_id'));

        $waitlistStudent->handle($enrollment, $group, $request->user(), $request->input('notes'));

        Toast::info(tkey('education.groups.messages.student_waitlisted'));

        return redirect()->route('platform.education.groups.edit', $group);
    }

    public function removeStudent(
        RemoveStudentFromTrainingGroupRequest $request,
        RemoveStudentFromTrainingGroupAction $removeStudent,
    ): RedirectResponse {
        $membership = TrainingGroupMembership::query()->findOrFail($request->integer('membership_id'));
        $groupId = $membership->training_group_id;

        $removeStudent->handle($membership, $request->user(), $request->input('reason'));

        Toast::info(tkey('education.groups.messages.student_removed'));

        return redirect()->route('platform.education.groups.edit', $groupId);
    }

    public function transferStudent(
        TransferStudentBetweenGroupsRequest $request,
        TransferStudentBetweenGroupsAction $transferStudent,
    ): RedirectResponse {
        $membership = TrainingGroupMembership::query()->findOrFail($request->integer('membership_id'));
        $transferStudent->handle($membership, $request->integer('target_group_id'), $request->user(), $request->boolean('allow_overbooking'), $request->input('reason'));

        Toast::info(tkey('education.groups.messages.student_transferred'));

        return redirect()->route('platform.education.groups.edit', $request->integer('target_group_id'));
    }

    public function completeMembership(
        CompleteTrainingGroupMembershipRequest $request,
        CompleteTrainingGroupMembershipAction $completeMembership,
    ): RedirectResponse {
        $membership = TrainingGroupMembership::query()->findOrFail($request->integer('membership_id'));
        $groupId = $membership->training_group_id;

        $completeMembership->handle($membership, $request->user());

        Toast::info(tkey('education.groups.messages.membership_completed'));

        return redirect()->route('platform.education.groups.edit', $groupId);
    }

    public function createSchedulePattern(
        StoreTrainingGroupSchedulePatternRequest $request,
        CreateTrainingGroupSchedulePatternAction $createPattern,
    ): RedirectResponse {
        $pattern = $createPattern->handle($request->patternData(), $request->user());

        Toast::info(tkey('education.groups.messages.schedule_pattern_created'));

        return redirect()->route('platform.education.groups.edit', $pattern->training_group_id);
    }

    public function deleteSchedulePattern(
        DeleteTrainingGroupSchedulePatternRequest $request,
        DeleteTrainingGroupSchedulePatternAction $deletePattern,
    ): RedirectResponse {
        $pattern = TrainingGroupSchedulePattern::query()->findOrFail($request->integer('pattern_id'));
        $groupId = $pattern->training_group_id;

        $deletePattern->handle($pattern, $request->user());

        Toast::info(tkey('education.groups.messages.schedule_pattern_deleted'));

        return redirect()->route('platform.education.groups.edit', $groupId);
    }

    public function assignLearningProgram(
        AssignLearningProgramToGroupRequest $request,
        AssignLearningProgramToGroupAction $assignProgram,
    ): RedirectResponse {
        $group = TrainingGroup::query()->findOrFail($request->integer('group_id'));
        $assignProgram->handle($group, $request->integer('learning_program_id'), $request->user());

        Toast::info(tkey('education.groups.messages.learning_program_assigned'));

        return redirect()->route('platform.education.groups.edit', $group);
    }

    public function recalculateCapacity(Request $request, RecalculateTrainingGroupCapacityAction $recalculate): RedirectResponse
    {
        abort_unless($request->user()?->hasAnyAccess(['education.groups.update', 'education.groups.manage_students']), 403);

        $group = TrainingGroup::query()->findOrFail((int) $request->input('group_id'));
        $recalculate->handle($group, $request->user());

        Toast::info(tkey('education.groups.messages.capacity_recalculated'));

        return redirect()->route('platform.education.groups.edit', $group);
    }

    public function publishOnSite(PublishTrainingGroupRequest $request, PublishTrainingGroupOnSiteAction $publish): RedirectResponse
    {
        $group = TrainingGroup::query()->findOrFail($request->integer('group_id'));
        $publish->handle($group, $request->user());

        Toast::info(tkey('education.groups.messages.published_on_site'));

        return redirect()->route('platform.education.groups.edit', $group);
    }

    public function hideFromSite(HideTrainingGroupRequest $request, HideTrainingGroupFromSiteAction $hide): RedirectResponse
    {
        $group = TrainingGroup::query()->findOrFail($request->integer('group_id'));
        $hide->handle($group, $request->user());

        Toast::info(tkey('education.groups.messages.hidden_from_site'));

        return redirect()->route('platform.education.groups.edit', $group);
    }

    public function addNote(AddTrainingGroupNoteRequest $request, AddTrainingGroupNoteAction $addNote): RedirectResponse
    {
        $group = $request->group();
        $addNote->handle($group, $request->body(), $request->user());

        Toast::info(tkey('education.groups.messages.note_added'));

        return redirect()->route('platform.education.groups.edit', $group);
    }

    public function archive(ArchiveTrainingGroupRequest $request, ArchiveTrainingGroupAction $archive): RedirectResponse
    {
        $group = TrainingGroup::query()->findOrFail($request->integer('group_id'));
        $archive->handle($group, $request->user(), $request->boolean('override_active_memberships'));

        Toast::info(tkey('education.groups.messages.archived'));

        return redirect()->route('platform.education.groups');
    }

    private function persist(
        TrainingGroupRequest $request,
        CreateTrainingGroupAction $createGroup,
        UpdateTrainingGroupAction $updateGroup,
    ): TrainingGroup {
        $data = $request->groupData();

        if (filled($request->input('group.id'))) {
            $group = TrainingGroup::query()->findOrFail($request->integer('group.id'));

            return $updateGroup->handle($group, $data, $request->user());
        }

        return $createGroup->handle($data, $request->user());
    }

    private function authorizeScreen(TrainingGroup $group): void
    {
        $permission = $group->exists ? 'education.groups.update' : 'education.groups.create';

        abort_unless(request()->user()?->hasAccess($permission), 403);
    }

    private function loadOptions(TrainingGroup $group): void
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

        $this->learningPrograms = LearningProgram::query()
            ->active()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->limit(200)
            ->get(['id', 'code', 'name_translations', 'sort_order'])
            ->mapWithKeys(fn (LearningProgram $program): array => [$program->id => $program->display_name])
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

        $this->targetGroups = TrainingGroup::query()
            ->operationalList()
            ->whereKeyNot($group->getKey() ?? 0)
            ->openForEnrollment()
            ->orderBy('start_date')
            ->limit(200)
            ->get()
            ->mapWithKeys(fn (TrainingGroup $targetGroup): array => [$targetGroup->id => $targetGroup->displayName()])
            ->all();
    }

    /**
     * @return array<int, mixed>
     */
    private function modals(): array
    {
        return [
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
                TextArea::make('comment')
                    ->title(tkey('education.groups.memberships.fields.notes'))
                    ->rows(3),
            ]))
                ->title(tkey('education.groups.actions.change_status'))
                ->method('changeStatus')
                ->applyButton(tkey('education.groups.actions.change_status'))
                ->canSee($this->group?->exists ?? false),

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
                TextArea::make('membership.notes')
                    ->title(tkey('education.groups.memberships.fields.notes'))
                    ->rows(3),
            ]))
                ->title(tkey('education.groups.actions.add_student'))
                ->method('addStudent')
                ->applyButton(tkey('education.groups.actions.add_student'))
                ->canSee($this->group?->exists ?? false),

            Layout::modal('waitlistStudentModal', Layout::rows([
                Input::make('training_group_id')->type('hidden'),
                Select::make('enrollment_id')
                    ->title(tkey('education.groups.memberships.fields.enrollment'))
                    ->options($this->enrollments)
                    ->required(),
                TextArea::make('notes')
                    ->title(tkey('education.groups.memberships.fields.notes'))
                    ->rows(3),
            ]))
                ->title(tkey('education.groups.actions.waitlist_student'))
                ->method('waitlistStudent')
                ->applyButton(tkey('education.groups.actions.waitlist_student'))
                ->canSee($this->group?->exists ?? false),

            Layout::modal('transferStudentModal', Layout::rows([
                Input::make('membership_id')->type('hidden'),
                Select::make('target_group_id')
                    ->title(tkey('education.groups.memberships.fields.transfer_to_group'))
                    ->options($this->targetGroups)
                    ->required(),
                Select::make('allow_overbooking')
                    ->title(tkey('education.groups.fields.allow_overbooking'))
                    ->empty(tkey('common.status.no'), '0')
                    ->options(['1' => tkey('common.status.yes')]),
                TextArea::make('reason')
                    ->title(tkey('education.groups.memberships.fields.transfer_reason'))
                    ->rows(3),
            ]))
                ->title(tkey('education.groups.actions.transfer_student'))
                ->method('transferStudent')
                ->applyButton(tkey('education.groups.actions.transfer_student'))
                ->canSee($this->group?->exists ?? false),

            Layout::modal('createSchedulePatternModal', Layout::rows([
                Input::make('pattern.training_group_id')->type('hidden'),
                Select::make('pattern.type')
                    ->title(tkey('education.schedule_patterns.fields.type'))
                    ->options($this->schedulePatternTypes())
                    ->required(),
                Select::make('pattern.day_of_week')
                    ->title(tkey('education.schedule_patterns.fields.day_of_week'))
                    ->options($this->days())
                    ->required(),
                Input::make('pattern.start_time')
                    ->type('time')
                    ->title(tkey('education.schedule_patterns.fields.start_time'))
                    ->required(),
                Input::make('pattern.end_time')
                    ->type('time')
                    ->title(tkey('education.schedule_patterns.fields.end_time'))
                    ->required(),
                Input::make('pattern.location_translations.en')
                    ->title(tkey('education.schedule_patterns.fields.location')),
                TextArea::make('pattern.notes_translations.en')
                    ->title(tkey('education.schedule_patterns.fields.notes'))
                    ->rows(3),
                Switcher::make('pattern.is_active')
                    ->sendTrueOrFalse()
                    ->title(tkey('education.schedule_patterns.fields.is_active')),
            ]))
                ->title(tkey('education.groups.actions.create_schedule_pattern'))
                ->method('createSchedulePattern')
                ->applyButton(tkey('education.groups.actions.create_schedule_pattern'))
                ->canSee($this->group?->exists ?? false),

            Layout::modal('assignLearningProgramModal', Layout::rows([
                Input::make('group_id')->type('hidden'),
                Select::make('learning_program_id')
                    ->title(tkey('education.groups.fields.learning_program'))
                    ->options($this->learningPrograms)
                    ->required(),
            ]))
                ->title(tkey('education.groups.actions.assign_learning_program'))
                ->method('assignLearningProgram')
                ->applyButton(tkey('education.groups.actions.assign_learning_program'))
                ->canSee($this->group?->exists ?? false),

            Layout::modal('addNoteModal', Layout::rows([
                Input::make('group_id')->type('hidden'),
                TextArea::make('body')
                    ->title(tkey('education.groups.actions.add_note'))
                    ->rows(4)
                    ->required(),
            ]))
                ->title(tkey('education.groups.actions.add_note'))
                ->method('addNote')
                ->applyButton(tkey('education.groups.actions.add_note'))
                ->canSee($this->group?->exists ?? false),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function schedulePatternTypes(): array
    {
        return [
            'theory' => tkey('education.schedule_patterns.types.theory'),
            'practice' => tkey('education.schedule_patterns.types.practice'),
            'consultation' => tkey('education.schedule_patterns.types.consultation'),
            'exam_preparation' => tkey('education.schedule_patterns.types.exam_preparation'),
            'other' => tkey('education.schedule_patterns.types.other'),
        ];
    }

    /**
     * @return array<int, string>
     */
    private function days(): array
    {
        return [
            1 => tkey('common.days.monday'),
            2 => tkey('common.days.tuesday'),
            3 => tkey('common.days.wednesday'),
            4 => tkey('common.days.thursday'),
            5 => tkey('common.days.friday'),
            6 => tkey('common.days.saturday'),
            7 => tkey('common.days.sunday'),
        ];
    }

    private function hasAccess(string $permission): bool
    {
        return request()->user()?->hasAccess($permission) ?? false;
    }
}
