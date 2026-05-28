<?php

namespace App\Orchid\Screens\School;

use App\Actions\CancelExamSessionAction;
use App\Models\ExamSession;
use App\Orchid\Screens\School\Concerns\InteractsWithExamScreens;
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

class ExamSessionListScreen extends Screen
{
    use InteractsWithExamScreens;

    /**
     * @var array<string, mixed>
     */
    private array $filters = [];

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

    public function query(Request $request): iterable
    {
        $this->filters = $this->filtersFromRequest($request);
        $this->types = $this->examTypeOptions(false);
        $this->statuses = $this->examStatusOptions(false);
        $this->branches = $this->branchOptions();
        $this->groups = $this->groupOptions();
        $this->examiners = $this->examinerOptions();

        return [
            'sessions' => $this->sessionQuery()
                ->orderByDesc('scheduled_at')
                ->simplePaginate(20)
                ->withQueryString(),
        ];
    }

    public function name(): ?string
    {
        return tkey('exams.sessions.title');
    }

    public function description(): ?string
    {
        return tkey('operations.exams.description');
    }

    public function permission(): iterable
    {
        return ['exams.sessions.view'];
    }

    public function commandBar(): iterable
    {
        return [
            Link::make(tkey('exams.actions.create'))
                ->icon('bs.plus-circle')
                ->route('platform.exams.sessions.create')
                ->canSee(request()->user()?->hasAccess('exams.sessions.create') ?? false),

            Button::make(tkey('exams.actions.export_csv'))
                ->icon('bs.download')
                ->method('export')
                ->canSee(request()->user()?->hasAccess('exams.export') ?? false),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::rows([
                Select::make('type_id')
                    ->title(tkey('exams.fields.type'))
                    ->empty(tkey('exams.filters.all_types'), '')
                    ->options($this->types)
                    ->value($this->filters['type_id'] ?? ''),
                Select::make('status_id')
                    ->title(tkey('exams.fields.status'))
                    ->empty(tkey('exams.filters.all_statuses'), '')
                    ->options($this->statuses)
                    ->value($this->filters['status_id'] ?? ''),
                Select::make('branch_id')
                    ->title(tkey('exams.fields.branch'))
                    ->empty(tkey('exams.filters.all_branches'), '')
                    ->options($this->branches)
                    ->value($this->filters['branch_id'] ?? ''),
                Select::make('group_id')
                    ->title(tkey('exams.fields.group'))
                    ->empty(tkey('exams.filters.all_groups'), '')
                    ->options($this->groups)
                    ->value($this->filters['group_id'] ?? ''),
                Select::make('examiner_id')
                    ->title(tkey('exams.fields.examiner'))
                    ->empty(tkey('exams.filters.all_examiners'), '')
                    ->options($this->examiners)
                    ->value($this->filters['examiner_id'] ?? ''),
                Input::make('date_from')
                    ->type('date')
                    ->title(tkey('exams.filters.date_from'))
                    ->value($this->filters['date_from'] ?? ''),
                Input::make('date_to')
                    ->type('date')
                    ->title(tkey('exams.filters.date_to'))
                    ->value($this->filters['date_to'] ?? ''),
            ])->title(tkey('exams.sections.filters')),

            Layout::table('sessions', [
                TD::make('exam_number', tkey('exams.fields.exam_number'))
                    ->sort()
                    ->render(fn (ExamSession $session): string => (string) Link::make($session->exam_number)
                        ->route('platform.exams.sessions.edit', $session)),
                TD::make('type_id', tkey('exams.fields.type'))
                    ->render(fn (ExamSession $session): string => $this->sessionTypeLabel($session)),
                TD::make('status_id', tkey('exams.fields.status'))
                    ->render(fn (ExamSession $session): string => $this->sessionStatusLabel($session)),
                TD::make('branch_id', tkey('exams.fields.branch'))
                    ->render(fn (ExamSession $session): string => $session->branch?->displayName() ?? $this->dash()),
                TD::make('group_id', tkey('exams.fields.group'))
                    ->render(fn (ExamSession $session): string => $session->groupAlias?->displayName() ?? $session->group?->displayName() ?? $this->dash()),
                TD::make('scheduled_at', tkey('exams.fields.scheduled_at'))
                    ->sort()
                    ->render(fn (ExamSession $session): string => $this->dateTime($session->scheduled_at ?? $session->starts_at)),
                TD::make('capacity', tkey('exams.fields.capacity'))
                    ->alignCenter()
                    ->render(fn (ExamSession $session): string => (string) $session->capacity),
                TD::make('participants_count', tkey('exams.fields.participants_count'))
                    ->alignCenter()
                    ->render(fn (ExamSession $session): string => (string) $session->participants_count),
                TD::make('examiner_id', tkey('exams.fields.examiner'))
                    ->render(fn (ExamSession $session): string => $session->examiner?->name ?? $this->dash()),
                TD::make('actions', tkey('exams.fields.actions'))
                    ->alignRight()
                    ->render(fn (ExamSession $session): DropDown => DropDown::make()
                        ->icon('bs.three-dots-vertical')
                        ->list([
                            Link::make(tkey('exams.actions.open'))
                                ->icon('bs.box-arrow-up-right')
                                ->route('platform.exams.sessions.edit', $session),
                            Link::make(tkey('common.actions.edit'))
                                ->icon('bs.pencil')
                                ->route('platform.exams.sessions.edit', $session)
                                ->canSee(request()->user()?->hasAccess('exams.sessions.update') ?? false),
                            Link::make(tkey('exams.actions.add_student'))
                                ->icon('bs.person-plus')
                                ->route('platform.exams.sessions.edit', $session)
                                ->canSee(request()->user()?->hasAccess('exams.sessions.update') ?? false),
                            Button::make(tkey('exams.actions.cancel'))
                                ->icon('bs.x-circle')
                                ->method('cancel')
                                ->parameters(['exam_session_id' => $session->id])
                                ->confirm(tkey('exams.messages.cancel_confirm'))
                                ->canSee(request()->user()?->hasAccess('exams.sessions.cancel') ?? false),
                        ])),
            ]),
        ];
    }

    public function cancel(Request $request, CancelExamSessionAction $cancel): RedirectResponse
    {
        abort_unless($request->user()?->hasAccess('exams.sessions.cancel'), 403);

        $session = ExamSession::query()->findOrFail($request->integer('exam_session_id'));
        $cancel->handle($session, $request->user(), $request->string('reason')->toString() ?: null);

        Toast::info(tkey('exams.messages.session_cancelled'));

        return redirect()->route('platform.exams.sessions');
    }

    public function export(Request $request): StreamedResponse
    {
        abort_unless($request->user()?->hasAccess('exams.export'), 403);

        $this->filters = $this->filtersFromRequest($request);

        return response()->streamDownload(function (): void {
            $handle = fopen('php://output', 'w');

            if ($handle === false) {
                return;
            }

            fputcsv($handle, [
                tkey('exams.fields.exam_number'),
                tkey('exams.fields.type'),
                tkey('exams.fields.status'),
                tkey('exams.fields.branch'),
                tkey('exams.fields.group'),
                tkey('exams.fields.scheduled_at'),
                tkey('exams.fields.capacity'),
                tkey('exams.fields.participants_count'),
                tkey('exams.fields.examiner'),
            ]);

            $this->sessionQuery()
                ->orderByDesc('scheduled_at')
                ->cursor()
                ->each(function (ExamSession $session) use ($handle): void {
                    fputcsv($handle, [
                        $session->exam_number,
                        $this->sessionTypeLabel($session),
                        $this->sessionStatusLabel($session),
                        $session->branch?->displayName() ?? '',
                        $session->groupAlias?->displayName() ?? $session->group?->displayName() ?? '',
                        $this->dateTime($session->scheduled_at ?? $session->starts_at),
                        $session->capacity,
                        $session->participants_count,
                        $session->examiner?->name ?? '',
                    ]);
                });

            fclose($handle);
        }, 'exam-sessions.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function sessionQuery(): Builder
    {
        return ExamSession::query()
            ->forExamList()
            ->with([
                'typeRecord:id,code,name,name_translations',
                'statusRecord:id,code,name,name_translations',
                'branch:id,name,name_translations,city,city_translations',
                'group:id,group_number,name,name_translations',
                'groupAlias:id,group_number,name,name_translations',
                'examiner:id,name',
            ])
            ->withCount('participants')
            ->when(filled($this->filters['type_id'] ?? null), fn (Builder $query): Builder => $query->where('type_id', $this->filters['type_id']))
            ->when(filled($this->filters['status_id'] ?? null), fn (Builder $query): Builder => $query->where('status_id', $this->filters['status_id']))
            ->when(filled($this->filters['branch_id'] ?? null), fn (Builder $query): Builder => $query->where('branch_id', $this->filters['branch_id']))
            ->when(filled($this->filters['group_id'] ?? null), fn (Builder $query): Builder => $query->where(function (Builder $groupQuery): void {
                $groupQuery->where('group_id', $this->filters['group_id'])
                    ->orWhere('training_group_id', $this->filters['group_id']);
            }))
            ->when(filled($this->filters['examiner_id'] ?? null), fn (Builder $query): Builder => $query->where('examiner_id', $this->filters['examiner_id']))
            ->when(($this->filters['type_scope'] ?? '') === 'internal', fn (Builder $query): Builder => $query->whereHas('typeRecord', fn (Builder $type): Builder => $type->where('is_internal', true)))
            ->when(($this->filters['type_scope'] ?? '') === 'official', fn (Builder $query): Builder => $query->whereHas('typeRecord', fn (Builder $type): Builder => $type->where('is_official', true)))
            ->when(filled($this->filters['date_from'] ?? null), fn (Builder $query): Builder => $query->whereDate('scheduled_at', '>=', $this->filters['date_from']))
            ->when(filled($this->filters['date_to'] ?? null), fn (Builder $query): Builder => $query->whereDate('scheduled_at', '<=', $this->filters['date_to']));
    }

    /**
     * @return array<string, mixed>
     */
    private function filtersFromRequest(Request $request): array
    {
        return collect($request->only([
            'type_id',
            'status_id',
            'branch_id',
            'group_id',
            'examiner_id',
            'date_from',
            'date_to',
            'type_scope',
        ]))
            ->filter(fn (mixed $value): bool => filled($value))
            ->all();
    }
}
