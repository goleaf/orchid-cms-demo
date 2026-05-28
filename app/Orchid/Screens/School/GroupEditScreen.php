<?php

declare(strict_types=1);

namespace App\Orchid\Screens\School;

use App\Actions\SaveTrainingGroupAction;
use App\Actions\AddStudentToTrainingGroupAction;
use App\Enums\GroupStatus;
use App\Http\Requests\Education\TrainingGroupMembershipRequest;
use App\Http\Requests\TrainingGroupRequest;
use App\Models\Branch;
use App\Models\Instructor;
use App\Models\StudentEnrollment;
use App\Models\TrainingGroup;
use App\Models\TrainingGroupActivity;
use App\Models\TrainingGroupMembership;
use App\Models\TrainingGroupSchedulePattern;
use App\Models\TrainingGroupStatus;
use App\Models\TrainingProgram;
use App\Orchid\Support\TranslatableFields;
use Illuminate\Http\RedirectResponse;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class GroupEditScreen extends Screen
{
    /**
     * @var TrainingGroup|null
     */
    public $group = null;

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
    private array $instructors = [];

    /**
     * @var array<int, string>
     */
    private array $statuses = [];

    /**
     * @var array<int, string>
     */
    private array $enrollments = [];

    public function query(?TrainingGroup $group = null): iterable
    {
        $groupModel = $group?->exists
            ? $group->loadMissing([
                'memberships.student:id,first_name,last_name,full_name,student_number',
                'memberships.enrollment:id,enrollment_number,student_profile_id',
                'schedulePatterns',
                'activities.user:id,name',
            ])
            : new TrainingGroup([
                'status' => GroupStatus::Open,
                'capacity' => 12,
                'places_taken' => 0,
                'is_visible_on_site' => true,
            ]);
        $this->group = $groupModel;

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
        $this->instructors = Instructor::query()
            ->forAdminList()
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
        $this->statuses = TrainingGroupStatus::query()
            ->active()
            ->ordered()
            ->get(['id', 'code', 'name', 'name_translations'])
            ->mapWithKeys(fn (TrainingGroupStatus $status): array => [$status->id => $status->displayName()])
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
                $enrollment->id => trim($enrollment->enrollment_number.' '.$enrollment->student?->display_name.' '.$enrollment->trainingProgram?->displayTitle()),
            ])
            ->all();

        return [
            'group' => $groupModel,
            'group.status' => $groupModel->status instanceof GroupStatus
                ? $groupModel->status->value
                : ($groupModel->status ?? GroupStatus::Open->value),
            'group.meeting_days' => implode(', ', $groupModel->meeting_days ?? []),
            'group.meeting_time' => $groupModel->meeting_time?->format('H:i'),
            'group.end_time' => $groupModel->end_time?->format('H:i'),
            'membership.training_group_id' => $groupModel->id,
            'name_translations' => $groupModel->getTranslations('name') ?: ['ru' => $groupModel->name],
            'memberships' => $groupModel->memberships ?? collect(),
            'schedulePatterns' => $groupModel->schedulePatterns ?? collect(),
            'activities' => $groupModel->activities ?? collect(),
        ];
    }

    public function name(): ?string
    {
        return $this->group?->exists
            ? tkey('website.admin.groups.edit_title')
            : tkey('website.admin.groups.create_title');
    }

    public function description(): ?string
    {
        return tkey('website.admin.groups.description');
    }

    public function permission(): iterable
    {
        return ['platform.operations.groups', 'website.manage_groups', 'education.groups.create', 'education.groups.update'];
    }

    public function commandBar(): iterable
    {
        return [
            Link::make(tkey('common.actions.back'))
                ->icon('bs.arrow-left')
                ->route('platform.website.groups'),

            Button::make(tkey('common.actions.save'))
                ->icon('bs.check-lg')
                ->method('save'),

            ModalToggle::make(tkey('education.groups.actions.add_member'))
                ->icon('bs.person-plus')
                ->modal('addMemberModal')
                ->method('addMember')
                ->canSee($this->group?->exists && (request()->user()?->hasAccess('education.manage_memberships') ?? false)),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::rows([
                Input::make('group.id')->type('hidden'),
                Input::make('group.code')
                    ->title(tkey('website.groups.columns.code'))
                    ->required(),
                Select::make('group.training_program_id')
                    ->title(tkey('crm.leads.fields.course'))
                    ->options($this->programs)
                    ->required(),
                Select::make('group.branch_id')
                    ->title(tkey('crm.leads.fields.branch'))
                    ->options($this->branches)
                    ->required(),
                Select::make('group.instructor_id')
                    ->title(tkey('crm.leads.fields.instructor'))
                    ->options($this->instructors)
                    ->empty(tkey('crm.leads.empty.no_instructor')),
                Select::make('group.status')
                    ->title(tkey('crm.leads.fields.status'))
                    ->options(GroupStatus::options())
                    ->required(),
                Select::make('group.status_id')
                    ->title(tkey('education.groups.fields.status_dictionary'))
                    ->options($this->statuses)
                    ->empty(tkey('students.filters.no_segment')),
                Input::make('group.capacity')
                    ->type('number')
                    ->title(tkey('website.admin.groups.fields.capacity'))
                    ->required(),
                Input::make('group.places_taken')
                    ->type('number')
                    ->title(tkey('website.admin.groups.fields.places_taken')),
                Input::make('group.starts_on')
                    ->type('date')
                    ->title(tkey('website.groups.columns.start')),
                Input::make('group.ends_on')
                    ->type('date')
                    ->title(tkey('website.admin.groups.fields.ends_on')),
                Input::make('group.enrollment_closes_on')
                    ->type('date')
                    ->title(tkey('education.groups.fields.enrollment_closes_on')),
                Input::make('group.meeting_days')
                    ->title(tkey('website.groups.columns.days')),
                Input::make('group.meeting_time')
                    ->type('time')
                    ->title(tkey('website.groups.columns.time')),
                Input::make('group.end_time')
                    ->type('time')
                    ->title(tkey('education.schedule_patterns.fields.ends_at')),
                Input::make('group.classroom')
                    ->title(tkey('website.admin.groups.fields.classroom')),
                Input::make('group.learning_notes')
                    ->title(tkey('education.groups.fields.learning_notes')),
                Input::make('group.schedule_notes')
                    ->title(tkey('education.groups.fields.schedule_notes')),
                Select::make('group.is_visible_on_site')
                    ->title(tkey('website.admin.groups.fields.is_visible_on_site'))
                    ->options([
                        1 => tkey('common.status.yes'),
                        0 => tkey('common.status.no'),
                    ]),
            ])->title(tkey('website.admin.sections.system')),

            TranslatableFields::input('name', 'website.admin.groups.fields.name', [
                'title_key' => 'website.admin.sections.content',
                'maxlength' => 255,
                'required' => true,
            ]),

            Layout::table('memberships', [
                TD::make('student', tkey('education.memberships.fields.student'))
                    ->render(fn (TrainingGroupMembership $membership): string => $membership->student?->display_name ?? '-'),
                TD::make('enrollment', tkey('education.memberships.fields.enrollment'))
                    ->render(fn (TrainingGroupMembership $membership): string => $membership->enrollment?->display_name ?? '-'),
                TD::make('status', tkey('education.memberships.fields.status'))
                    ->render(fn (TrainingGroupMembership $membership): string => tkey('education.memberships.statuses.'.$membership->status)),
                TD::make('joined_at', tkey('education.memberships.fields.joined_at'))
                    ->render(fn (TrainingGroupMembership $membership): string => $membership->joined_at?->format('Y-m-d H:i') ?? '-'),
            ])->title(tkey('education.groups.sections.memberships')),

            Layout::table('schedulePatterns', [
                TD::make('day_of_week', tkey('education.schedule_patterns.fields.day_of_week'))
                    ->render(fn (TrainingGroupSchedulePattern $pattern): string => (string) $pattern->day_of_week),
                TD::make('starts_at', tkey('education.schedule_patterns.fields.starts_at'))
                    ->render(fn (TrainingGroupSchedulePattern $pattern): string => $pattern->starts_at?->format('H:i') ?? '-'),
                TD::make('ends_at', tkey('education.schedule_patterns.fields.ends_at'))
                    ->render(fn (TrainingGroupSchedulePattern $pattern): string => $pattern->ends_at?->format('H:i') ?? '-'),
                TD::make('lesson_type', tkey('education.schedule_patterns.fields.lesson_type'))
                    ->render(fn (TrainingGroupSchedulePattern $pattern): string => tkey('education.learning_topics.types.'.$pattern->lesson_type)),
            ])->title(tkey('education.groups.sections.schedule')),

            Layout::table('activities', [
                TD::make('created_at', tkey('students.fields.created_at'))
                    ->render(fn (TrainingGroupActivity $activity): string => $activity->created_at->format('Y-m-d H:i')),
                TD::make('type', tkey('crm.leads.fields.status'))
                    ->render(fn (TrainingGroupActivity $activity): string => $activity->display_type),
                TD::make('user', tkey('students.fields.updated_by'))
                    ->render(fn (TrainingGroupActivity $activity): string => $activity->user?->name ?? '-'),
            ])->title(tkey('education.groups.sections.activities')),

            Layout::modal('addMemberModal', [
                Layout::rows([
                    Input::make('membership.training_group_id')->type('hidden'),
                    Select::make('membership.enrollment_id')
                        ->title(tkey('education.memberships.fields.enrollment'))
                        ->options($this->enrollments)
                        ->required(),
                    Select::make('membership.allow_overbooking')
                        ->title(tkey('students.enrollments.fields.allow_overbooking'))
                        ->options([
                            0 => tkey('common.status.no'),
                            1 => tkey('common.status.yes'),
                        ]),
                ]),
            ])
                ->title(tkey('education.groups.actions.add_member'))
                ->applyButton(tkey('education.groups.actions.add_member')),
        ];
    }

    public function save(TrainingGroupRequest $request, SaveTrainingGroupAction $save): RedirectResponse
    {
        $group = filled($request->input('group.id'))
            ? TrainingGroup::query()->findOrFail($request->integer('group.id'))
            : new TrainingGroup;

        $save->handle($group, $request->groupData(), $request->user());

        Toast::info(tkey('education.groups.messages.saved'));

        return redirect()->route('platform.website.groups');
    }

    public function addMember(TrainingGroupMembershipRequest $request, AddStudentToTrainingGroupAction $addToGroup): RedirectResponse
    {
        $data = $request->membershipData();
        $enrollment = StudentEnrollment::query()->findOrFail($data['enrollment_id']);

        $addToGroup->handle($enrollment, (int) $data['training_group_id'], $request->user(), (bool) $data['allow_overbooking']);

        Toast::info(tkey('education.groups.messages.member_added'));

        return redirect()->route('platform.website.groups.edit', $data['training_group_id']);
    }
}
