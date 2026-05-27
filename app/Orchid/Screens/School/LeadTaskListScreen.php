<?php

declare(strict_types=1);

namespace App\Orchid\Screens\School;

use App\Actions\CompleteLeadTaskAction;
use App\Enums\LeadTaskStatus;
use App\Models\MarketingLeadTask;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class LeadTaskListScreen extends Screen
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
            'assigned_to_user_id' => (string) $request->query('assigned_to_user_id', ''),
        ];

        $this->managers = User::query()
            ->select(['id', 'name'])
            ->orderBy('name')
            ->limit(100)
            ->pluck('name', 'id')
            ->all();

        $tasks = MarketingLeadTask::query()
            ->with([
                'marketingLead:id,first_name,last_name,phone,email,status,responsible_manager_id',
                'assignedTo:id,name',
                'createdBy:id,name',
            ])
            ->when($this->filters['assigned_to_user_id'] !== '', fn (Builder $query): Builder => $query
                ->where('assigned_to_user_id', $this->filters['assigned_to_user_id']))
            ->when($this->filters['segment'] === 'my' && $request->user() !== null, fn (Builder $query): Builder => $query
                ->where('assigned_to_user_id', $request->user()->id)
                ->whereIn('status', [LeadTaskStatus::Open->value, LeadTaskStatus::InProgress->value]))
            ->when($this->filters['segment'] === 'open', fn (Builder $query): Builder => $query
                ->whereIn('status', [LeadTaskStatus::Open->value, LeadTaskStatus::InProgress->value]))
            ->when($this->filters['segment'] === 'overdue', fn (Builder $query): Builder => $query->overdue())
            ->when($this->filters['segment'] === 'today', fn (Builder $query): Builder => $query
                ->whereIn('status', [LeadTaskStatus::Open->value, LeadTaskStatus::InProgress->value])
                ->whereDate('due_at', today()))
            ->when($this->filters['segment'] === 'done', fn (Builder $query): Builder => $query->where('status', LeadTaskStatus::Done->value))
            ->orderBy('due_at')
            ->simplePaginate(20)
            ->withQueryString();

        return [
            'tasks' => $tasks,
        ];
    }

    public function name(): ?string
    {
        return tkey('crm.tasks.title');
    }

    public function description(): ?string
    {
        return tkey('crm.tasks.description');
    }

    public function permission(): iterable
    {
        return ['crm.leads.view'];
    }

    public function commandBar(): iterable
    {
        return [
            Link::make(tkey('menu.crm.leads'))
                ->icon('bs.list-ul')
                ->route('platform.marketing.leads'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::rows([
                Select::make('segment')
                    ->title(tkey('crm.tasks.filters.segment'))
                    ->options([
                        'open' => tkey('crm.tasks.segments.open'),
                        'my' => tkey('crm.tasks.segments.my'),
                        'today' => tkey('crm.tasks.segments.today'),
                        'overdue' => tkey('crm.tasks.segments.overdue'),
                        'done' => tkey('crm.tasks.segments.done'),
                    ])
                    ->value($this->filters['segment'] ?? 'open'),

                Select::make('assigned_to_user_id')
                    ->title(tkey('crm.tasks.fields.assigned_to'))
                    ->empty(tkey('crm.leads.filters.all_managers'), '')
                    ->options($this->managers)
                    ->value($this->filters['assigned_to_user_id'] ?? ''),

                Button::make(tkey('common.actions.search'))
                    ->icon('bs.search')
                    ->method('filter')
                    ->novalidate(),
            ]),

            Layout::table('tasks', [
                TD::make('due_at', tkey('crm.tasks.fields.due_at'))
                    ->render(fn (MarketingLeadTask $task): string => $this->dueLabel($task)),
                TD::make('title', tkey('crm.tasks.fields.title'))
                    ->render(fn (MarketingLeadTask $task): string => $task->title),
                TD::make('lead', tkey('crm.leads.title'))
                    ->render(fn (MarketingLeadTask $task): string => $task->marketingLead
                        ? (string) Link::make($task->marketingLead->fullName())
                            ->route('platform.marketing.leads.edit', $task->marketingLead)
                        : '-'),
                TD::make('assignedTo', tkey('crm.tasks.fields.assigned_to'))
                    ->render(fn (MarketingLeadTask $task): string => $task->assignedTo?->name ?? '-'),
                TD::make('priority', tkey('crm.tasks.fields.priority'))
                    ->render(fn (MarketingLeadTask $task): string => $task->priority->label()),
                TD::make('status', tkey('crm.tasks.fields.status'))
                    ->render(fn (MarketingLeadTask $task): string => $task->status->label()),
                TD::make('actions', tkey('crm.leads.columns.actions'))
                    ->alignRight()
                    ->render(fn (MarketingLeadTask $task): string => (string) Button::make(tkey('crm.tasks.actions.complete'))
                        ->icon('bs.check2')
                        ->method('complete')
                        ->parameters(['task' => $task->id])
                        ->canSee($task->status !== LeadTaskStatus::Done)),
            ]),
        ];
    }

    public function filter(Request $request): RedirectResponse
    {
        return redirect()->route('platform.crm.tasks', array_filter([
            'segment' => $request->input('segment'),
            'assigned_to_user_id' => $request->input('assigned_to_user_id'),
        ], fn (mixed $value): bool => filled($value)));
    }

    public function complete(Request $request, CompleteLeadTaskAction $completeTask): RedirectResponse
    {
        $task = MarketingLeadTask::query()->findOrFail($request->integer('task'));

        $completeTask->handle($task, $request->user());

        Toast::info(tkey('crm.tasks.messages.completed'));

        return redirect()->route('platform.crm.tasks', $request->query());
    }

    private function dueLabel(MarketingLeadTask $task): string
    {
        $value = $task->due_at?->format('Y-m-d H:i') ?? '-';

        return $task->isOverdue()
            ? tkey('crm.tasks.labels.overdue_value', ['value' => $value])
            : $value;
    }
}
