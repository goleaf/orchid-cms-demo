<?php

declare(strict_types=1);

namespace App\Orchid\Screens\School;

use App\Actions\CreateOrUpdateTrainingGroupSchedulePatternAction;
use App\Http\Requests\Education\TrainingGroupSchedulePatternRequest;
use App\Models\TrainingGroup;
use App\Models\TrainingGroupSchedulePattern;
use App\Orchid\Support\TranslatableFields;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\Switcher;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class TrainingGroupSchedulePatternListScreen extends Screen
{
    public ?TrainingGroupSchedulePattern $pattern = null;

    /**
     * @var array<int, string>
     */
    private array $groups = [];

    public function query(Request $request): iterable
    {
        $this->pattern = $request->filled('pattern_id')
            ? TrainingGroupSchedulePattern::query()->findOrFail($request->integer('pattern_id'))
            : new TrainingGroupSchedulePattern([
                'day_of_week' => 1,
                'starts_at' => '18:00',
                'ends_at' => '20:00',
                'lesson_type' => 'theory',
                'is_active' => true,
                'sort_order' => 0,
            ]);

        $this->groups = TrainingGroup::query()
            ->operationalList()
            ->ordered()
            ->get()
            ->mapWithKeys(fn (TrainingGroup $group): array => [$group->id => $group->displayName()])
            ->all();

        return [
            'patterns' => TrainingGroupSchedulePattern::query()
                ->with('group:id,name,name_translations,code,group_number')
                ->ordered()
                ->simplePaginate(20)
                ->withQueryString(),
            'pattern' => $this->pattern,
            'pattern.title_translations' => $this->pattern->title_translations ?? [],
        ];
    }

    public function name(): ?string
    {
        return tkey('education.schedule_patterns.title');
    }

    public function description(): ?string
    {
        return tkey('education.schedule_patterns.description');
    }

    public function permission(): iterable
    {
        return ['education.manage_schedule_patterns'];
    }

    public function commandBar(): iterable
    {
        return [
            Link::make(tkey('common.actions.create'))
                ->icon('bs.plus-circle')
                ->route('platform.education.schedule-patterns'),
            Button::make(tkey('common.actions.save'))
                ->icon('bs.check2-circle')
                ->method('save'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::rows([
                Input::make('pattern.id')->type('hidden'),
                Select::make('pattern.training_group_id')
                    ->title(tkey('education.groups.fields.name'))
                    ->options($this->groups)
                    ->required(),
                Input::make('pattern.day_of_week')
                    ->type('number')
                    ->title(tkey('education.schedule_patterns.fields.day_of_week'))
                    ->required(),
                Input::make('pattern.starts_at')
                    ->type('time')
                    ->title(tkey('education.schedule_patterns.fields.start_time'))
                    ->required(),
                Input::make('pattern.ends_at')
                    ->type('time')
                    ->title(tkey('education.schedule_patterns.fields.end_time'))
                    ->required(),
                Select::make('pattern.lesson_type')
                    ->title(tkey('education.schedule_patterns.fields.type'))
                    ->options($this->lessonTypeOptions())
                    ->required(),
                Input::make('pattern.classroom')
                    ->title(tkey('education.schedule_patterns.fields.classroom')),
                Input::make('pattern.sort_order')
                    ->type('number')
                    ->title(tkey('crm.dictionaries.fields.sort_order')),
                Switcher::make('pattern.is_active')
                    ->sendTrueOrFalse()
                    ->title(tkey('crm.dictionaries.fields.is_active')),
            ])->title(tkey('education.schedule_patterns.title')),

            TranslatableFields::input('pattern.title', 'education.schedule_patterns.title', [
                'maxlength' => 255,
            ]),

            Layout::table('patterns', [
                TD::make('group', tkey('education.groups.fields.name'))
                    ->render(fn (TrainingGroupSchedulePattern $pattern): string => $pattern->group?->displayName() ?? '-'),
                TD::make('day_of_week', tkey('education.schedule_patterns.fields.day_of_week'))
                    ->render(fn (TrainingGroupSchedulePattern $pattern): string => $pattern->display_day),
                TD::make('starts_at', tkey('education.schedule_patterns.fields.start_time'))
                    ->render(fn (TrainingGroupSchedulePattern $pattern): string => $pattern->starts_at?->format('H:i') ?? '-'),
                TD::make('ends_at', tkey('education.schedule_patterns.fields.end_time'))
                    ->render(fn (TrainingGroupSchedulePattern $pattern): string => $pattern->ends_at?->format('H:i') ?? '-'),
                TD::make('lesson_type', tkey('education.schedule_patterns.fields.type'))
                    ->render(fn (TrainingGroupSchedulePattern $pattern): string => tkey('education.schedule_patterns.types.'.$pattern->lesson_type)),
            ]),
        ];
    }

    public function save(TrainingGroupSchedulePatternRequest $request, CreateOrUpdateTrainingGroupSchedulePatternAction $savePattern): RedirectResponse
    {
        $pattern = filled($request->input('pattern.id'))
            ? TrainingGroupSchedulePattern::query()->findOrFail($request->integer('pattern.id'))
            : new TrainingGroupSchedulePattern;

        $savePattern->handle($pattern, $request->patternData(), $request->user());

        Toast::info(tkey('education.groups.messages.saved'));

        return redirect()->route('platform.education.schedule-patterns');
    }

    /**
     * @return array<string, string>
     */
    private function lessonTypeOptions(): array
    {
        return collect(['theory', 'practice', 'consultation', 'exam_preparation', 'other'])
            ->mapWithKeys(fn (string $type): array => [$type => tkey('education.schedule_patterns.types.'.$type)])
            ->all();
    }
}
