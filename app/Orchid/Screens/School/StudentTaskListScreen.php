<?php

declare(strict_types=1);

namespace App\Orchid\Screens\School;

use App\Actions\CancelStudentTaskAction;
use App\Actions\CompleteStudentTaskAction;
use App\Http\Requests\Students\CancelStudentTaskRequest;
use App\Http\Requests\Students\CompleteStudentTaskRequest;
use App\Models\StudentTask;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class StudentTaskListScreen extends Screen
{
    /**
     * @var array<string, mixed>
     */
    private array $filters = [];

    /**
     * @var array<int, string>
     */
    private array $managers = [];

    public function query(Request $request): iterable
    {
        $this->filters = [
            'segment' => (string) $request->query('segment', 'open'),
            'assigned_to_id' => (string) $request->query('assigned_to_id', ''),
            'priority' => (string) $request->query('priority', ''),
            'status' => (string) $request->query('status', ''),
        ];

        $this->managers = User::query()
            ->select(['id', 'name'])
            ->orderBy('name')
            ->limit(100)
            ->pluck('name', 'id')
            ->all();

        return [
            'tasks' => StudentTask::query()
                ->with([
                    'student:id,student_number,first_name,last_name,full_name,phone,email',
                    'enrollment:id,enrollment_number,student_profile_id',
                    'assignedTo:id,name',
                    'createdBy:id,name',
                ])
                ->when($this->filters['assigned_to_id'] !== '', fn (Builder $query): Builder => $query->assignedTo($this->filters['assigned_to_id']))
                ->when($this->filters['priority'] !== '', fn (Builder $query): Builder => $query->where('priority', $this->filters['priority']))
                ->when($this->filters['status'] !== '', fn (Builder $query): Builder => $query->where('status', $this->filters['status']))
                ->when($this->filters['segment'] === 'my' && $request->user() !== null, fn (Builder $query): Builder => $query
                    ->assignedTo($request->user()->id)
                    ->open())
                ->when($this->filters['segment'] === 'open', fn (Builder $query): Builder => $query->open())
                ->when($this->filters['segment'] === 'overdue', fn (Builder $query): Builder => $query->overdue())
                ->when($this->filters['segment'] === 'today', fn (Builder $query): Builder => $query->dueToday())
                ->when($this->filters['segment'] === 'completed', fn (Builder $query): Builder => $query->completed())
                ->when($this->filters['segment'] === 'cancelled', fn (Builder $query): Builder => $query->cancelled())
                ->orderBy('due_at')
                ->simplePaginate(20)
                ->withQueryString(),
        ];
    }

    public function name(): ?string
    {
        return tkey('students.tasks.title');
    }

    public function description(): ?string
    {
        return tkey('students.tasks.description');
    }

    public function permission(): iterable
    {
        return ['students.manage_tasks', 'platform.crm.students'];
    }

    public function commandBar(): iterable
    {
        return [
            Link::make(tkey('menu.students.all'))
                ->icon('bs.person-lines-fill')
                ->route('platform.students'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::rows([
                Select::make('segment')
                    ->title(tkey('students.tasks.filters.segment'))
                    ->options([
                        'open' => tkey('students.tasks.segments.open'),
                        'my' => tkey('students.tasks.segments.my'),
                        'today' => tkey('students.tasks.segments.today'),
                        'overdue' => tkey('students.tasks.segments.overdue'),
                        'completed' => tkey('students.tasks.statuses.done'),
                        'cancelled' => tkey('students.tasks.statuses.cancelled'),
                    ])
                    ->value($this->filters['segment'] ?? 'open'),

                Select::make('assigned_to_id')
                    ->title(tkey('students.tasks.fields.assigned_to'))
                    ->empty(tkey('students.filters.all_managers'), '')
                    ->options($this->managers)
                    ->value($this->filters['assigned_to_id'] ?? ''),

                Select::make('priority')
                    ->title(tkey('students.tasks.fields.priority'))
                    ->empty(tkey('students.filters.all_priorities'), '')
                    ->options($this->priorityOptions())
                    ->value($this->filters['priority'] ?? ''),

                Select::make('status')
                    ->title(tkey('students.tasks.fields.status'))
                    ->empty(tkey('students.filters.all_statuses'), '')
                    ->options($this->statusOptions())
                    ->value($this->filters['status'] ?? ''),

                Button::make(tkey('common.actions.search'))
                    ->icon('bs.search')
                    ->method('filter')
                    ->novalidate(),
            ]),

            Layout::table('tasks', [
                TD::make('due_at', tkey('students.tasks.fields.due_at'))
                    ->render(fn (StudentTask $task): string => $this->taskDueLabel($task)),
                TD::make('title', tkey('students.tasks.fields.title'))
                    ->render(fn (StudentTask $task): string => $task->display_title),
                TD::make('student', tkey('students.tasks.fields.student'))
                    ->render(fn (StudentTask $task): string => $task->student
                        ? (string) Link::make($task->student->display_name)->route('platform.students.edit', $task->student)
                        : '-'),
                TD::make('enrollment', tkey('students.tasks.fields.enrollment'))
                    ->render(fn (StudentTask $task): string => $task->enrollment?->display_name ?? '-'),
                TD::make('assigned_to', tkey('students.tasks.fields.assigned_to'))
                    ->render(fn (StudentTask $task): string => $task->assignedTo?->name ?? '-'),
                TD::make('priority', tkey('students.tasks.fields.priority'))
                    ->render(fn (StudentTask $task): string => tkey('students.tasks.priorities.'.$task->priority)),
                TD::make('status', tkey('students.tasks.fields.status'))
                    ->render(fn (StudentTask $task): string => tkey('students.tasks.statuses.'.$task->status)),
                TD::make('completed_at', tkey('students.tasks.fields.completed_at'))
                    ->render(fn (StudentTask $task): string => $task->completed_at?->format('Y-m-d H:i') ?? '-'),
                TD::make('actions', tkey('crm.leads.columns.actions'))
                    ->alignRight()
                    ->render(fn (StudentTask $task): DropDown => DropDown::make()
                        ->icon('bs.three-dots-vertical')
                        ->list([
                            Link::make(tkey('students.actions.open'))
                                ->icon('bs.box-arrow-in-right')
                                ->route('platform.students.edit', $task->student),
                            Button::make(tkey('students.actions.complete_task'))
                                ->icon('bs.check2')
                                ->method('complete')
                                ->parameters(['task' => $task->id])
                                ->canSee(! in_array($task->status, ['done', 'cancelled'], true)),
                            Button::make(tkey('students.actions.cancel_task'))
                                ->icon('bs.x-circle')
                                ->method('cancel')
                                ->parameters(['task' => $task->id])
                                ->canSee(! in_array($task->status, ['done', 'cancelled'], true)),
                        ])),
            ]),
        ];
    }

    public function filter(Request $request): RedirectResponse
    {
        return redirect()->route('platform.students.tasks', array_filter([
            'segment' => $request->input('segment'),
            'assigned_to_id' => $request->input('assigned_to_id'),
            'priority' => $request->input('priority'),
            'status' => $request->input('status'),
        ], fn (mixed $value): bool => filled($value)));
    }

    public function complete(CompleteStudentTaskRequest $request, CompleteStudentTaskAction $completeTask): RedirectResponse
    {
        $task = StudentTask::query()->findOrFail((int) $request->input('task'));

        $completeTask->handle($task, $request->user());

        Toast::info(tkey('students.messages.task_completed'));

        return redirect()->route('platform.students.tasks', $request->query());
    }

    public function cancel(CancelStudentTaskRequest $request, CancelStudentTaskAction $cancelTask): RedirectResponse
    {
        $task = StudentTask::query()->findOrFail((int) $request->input('task'));

        $cancelTask->handle($task, $request->user());

        Toast::info(tkey('students.messages.task_cancelled'));

        return redirect()->route('platform.students.tasks', $request->query());
    }

    /**
     * @return array<string, string>
     */
    private function priorityOptions(): array
    {
        return collect(['low', 'normal', 'high', 'urgent'])
            ->mapWithKeys(fn (string $priority): array => [$priority => tkey('students.tasks.priorities.'.$priority)])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    private function statusOptions(): array
    {
        return collect(['open', 'in_progress', 'done', 'cancelled'])
            ->mapWithKeys(fn (string $status): array => [$status => tkey('students.tasks.statuses.'.$status)])
            ->all();
    }

    private function taskDueLabel(StudentTask $task): string
    {
        $value = $task->due_at?->format('Y-m-d H:i') ?? '-';

        return $task->is_overdue ? tkey('students.tasks.labels.overdue_value', ['value' => $value]) : $value;
    }
}
