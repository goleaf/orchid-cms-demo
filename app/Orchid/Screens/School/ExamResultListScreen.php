<?php

namespace App\Orchid\Screens\School;

use App\Models\ExamResult;
use App\Models\ExamAttempt;
use App\Orchid\Screens\School\Concerns\InteractsWithExamScreens;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class ExamResultListScreen extends Screen
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
    private array $groups = [];

    public function query(Request $request): iterable
    {
        $this->filters = collect($request->only(['type_id', 'student_id', 'group_id', 'passed']))
            ->filter(fn (mixed $value): bool => filled($value))
            ->all();
        $this->types = $this->examTypeOptions(false);
        $this->groups = $this->groupOptions();

        return [
            'results' => ExamResult::query()
                ->select([
                    'id',
                    'attempt_id',
                    'result_status_id',
                    'score',
                    'max_score',
                    'passed',
                    'decided_by_id',
                    'decided_at',
                ])
                ->with([
                    'resultStatus:id,code,name,name_translations',
                    'decidedBy:id,name',
                    'attempt:id,exam_session_id,enrollment_id,student_id,student_profile_id,training_group_id,attempt_no,attempt_number',
                    'attempt.session:id,exam_number,type_id,scheduled_at',
                    'attempt.session.typeRecord:id,code,name,name_translations',
                    'attempt.student:id,first_name,last_name,full_name,student_number',
                    'attempt.enrollment:id,enrollment_number,student_profile_id,training_program_id',
                ])
                ->when(filled($this->filters['type_id'] ?? null), fn (Builder $query): Builder => $query->whereHas('attempt.session', fn (Builder $session): Builder => $session->where('type_id', $this->filters['type_id'])))
                ->when(filled($this->filters['student_id'] ?? null), fn (Builder $query): Builder => $query->whereHas('attempt', fn (Builder $attempt): Builder => $attempt->where(function (Builder $studentQuery): void {
                    $studentQuery->where('student_id', $this->filters['student_id'])
                        ->orWhere('student_profile_id', $this->filters['student_id']);
                })))
                ->when(filled($this->filters['group_id'] ?? null), fn (Builder $query): Builder => $query->whereHas('attempt', fn (Builder $attempt): Builder => $attempt->where('training_group_id', $this->filters['group_id'])))
                ->when(($this->filters['passed'] ?? '') !== '', fn (Builder $query): Builder => $query->where('passed', (bool) $this->filters['passed']))
                ->orderByDesc('decided_at')
                ->simplePaginate(20)
                ->withQueryString(),
        ];
    }

    public function name(): ?string
    {
        return tkey('exams.results.title');
    }

    public function description(): ?string
    {
        return tkey('operations.exams.description');
    }

    public function permission(): iterable
    {
        return ['exams.results.view'];
    }

    public function commandBar(): iterable
    {
        return [];
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
                Input::make('student_id')
                    ->type('number')
                    ->title(tkey('exams.fields.student'))
                    ->value($this->filters['student_id'] ?? ''),
                Select::make('group_id')
                    ->title(tkey('exams.fields.group'))
                    ->empty(tkey('exams.filters.all_groups'), '')
                    ->options($this->groups)
                    ->value($this->filters['group_id'] ?? ''),
                Select::make('passed')
                    ->title(tkey('exams.fields.passed'))
                    ->empty(tkey('exams.filters.all_results'), '')
                    ->options($this->yesNoOptions())
                    ->value($this->filters['passed'] ?? ''),
            ])->title(tkey('exams.sections.filters')),

            Layout::table('results', [
                TD::make('attempt_id', tkey('exams.fields.attempt_no'))
                    ->render(fn (ExamResult $result): string => $this->attemptLink($result->attempt, $result->attempt_id)),
                TD::make('session', tkey('exams.fields.session'))
                    ->render(fn (ExamResult $result): string => $result->attempt?->session?->exam_number ?? $this->dash()),
                TD::make('type', tkey('exams.fields.type'))
                    ->render(fn (ExamResult $result): string => $result->attempt?->session === null ? $this->dash() : $this->sessionTypeLabel($result->attempt->session)),
                TD::make('student', tkey('exams.fields.student'))
                    ->render(fn (ExamResult $result): string => $result->attempt?->student?->display_name ?? $this->dash()),
                TD::make('enrollment', tkey('exams.fields.enrollment'))
                    ->render(fn (ExamResult $result): string => $this->enrollmentLabel($result->attempt?->enrollment)),
                TD::make('result_status_id', tkey('exams.fields.result_status'))
                    ->render(fn (ExamResult $result): string => $this->resultStatusLabel($result)),
                TD::make('score', tkey('exams.fields.score'))
                    ->render(fn (ExamResult $result): string => trim((string) $result->score.'/'.$result->max_score, '/')),
                TD::make('passed', tkey('exams.fields.passed'))
                    ->render(fn (ExamResult $result): string => $this->boolLabel($result->passed)),
                TD::make('decided_at', tkey('exams.fields.decided_at'))
                    ->render(fn (ExamResult $result): string => $this->dateTime($result->decided_at)),
            ]),
        ];
    }

    private function attemptLink(?ExamAttempt $attempt, mixed $fallback): string
    {
        if ($attempt === null) {
            return (string) ($fallback ?? $this->dash());
        }

        return (string) Link::make((string) ($attempt->attempt_no ?? $attempt->attempt_number ?? $attempt->id))
            ->route('platform.exams.attempts.edit', $attempt);
    }
}
