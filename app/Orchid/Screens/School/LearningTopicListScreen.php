<?php

declare(strict_types=1);

namespace App\Orchid\Screens\School;

use App\Actions\CreateOrUpdateLearningTopicAction;
use App\Http\Requests\Education\LearningTopicRequest;
use App\Models\LearningTopic;
use App\Models\TrainingProgram;
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

class LearningTopicListScreen extends Screen
{
    public ?LearningTopic $topic = null;

    /**
     * @var array<int, string>
     */
    private array $programs = [];

    public function query(Request $request): iterable
    {
        $this->topic = $request->filled('topic_id')
            ? LearningTopic::query()->findOrFail($request->integer('topic_id'))
            : new LearningTopic([
                'topic_type' => 'theory',
                'is_required' => true,
                'is_active' => true,
                'sort_order' => 0,
            ]);

        $this->programs = TrainingProgram::query()
            ->forAcademyList()
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get()
            ->mapWithKeys(fn (TrainingProgram $program): array => [$program->id => $program->displayTitle()])
            ->all();

        return [
            'topics' => LearningTopic::query()
                ->with('trainingProgram:id,title,title_translations,name_translations')
                ->ordered()
                ->simplePaginate(20)
                ->withQueryString(),
            'topic' => $this->topic,
            'topic.title_translations' => $this->topic->title_translations ?? [],
            'topic.description_translations' => $this->topic->description_translations ?? [],
        ];
    }

    public function name(): ?string
    {
        return tkey('education.learning_topics.title');
    }

    public function description(): ?string
    {
        return tkey('education.learning_topics.description');
    }

    public function permission(): iterable
    {
        return ['education.manage_topics'];
    }

    public function commandBar(): iterable
    {
        return [
            Link::make(tkey('common.actions.create'))
                ->icon('bs.plus-circle')
                ->route('platform.education.learning-topics'),
            Button::make(tkey('common.actions.save'))
                ->icon('bs.check2-circle')
                ->method('save'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::rows([
                Input::make('topic.id')->type('hidden'),
                Select::make('topic.training_program_id')
                    ->title(tkey('website.groups.fields.course'))
                    ->options($this->programs)
                    ->required(),
                Input::make('topic.code')
                    ->title(tkey('crm.dictionaries.fields.code')),
                Select::make('topic.topic_type')
                    ->title(tkey('education.learning_topics.fields.topic_type'))
                    ->options($this->topicTypeOptions())
                    ->required(),
                Input::make('topic.duration_minutes')
                    ->type('number')
                    ->title(tkey('education.learning_topics.fields.duration_minutes')),
                Input::make('topic.sort_order')
                    ->type('number')
                    ->title(tkey('crm.dictionaries.fields.sort_order')),
                Switcher::make('topic.is_required')
                    ->sendTrueOrFalse()
                    ->title(tkey('students.tasks.fields.required')),
                Switcher::make('topic.is_active')
                    ->sendTrueOrFalse()
                    ->title(tkey('crm.dictionaries.fields.is_active')),
            ])->title(tkey('education.learning_topics.title')),

            TranslatableFields::input('topic.title', 'crm.dictionaries.fields.name_translations', [
                'maxlength' => 255,
                'required' => true,
            ]),

            TranslatableFields::textarea('topic.description', 'crm.dictionaries.fields.description_translations', [
                'rows' => 3,
                'maxlength' => 2000,
            ]),

            Layout::table('topics', [
                TD::make('code', tkey('crm.dictionaries.fields.code'))
                    ->render(fn (LearningTopic $topic): string => (string) Link::make($topic->code ?: (string) $topic->id)
                        ->route('platform.education.learning-topics', ['topic_id' => $topic->id])),
                TD::make('title', tkey('crm.dictionaries.fields.name'))
                    ->render(fn (LearningTopic $topic): string => $topic->displayTitle()),
                TD::make('program', tkey('website.groups.fields.course'))
                    ->render(fn (LearningTopic $topic): string => $topic->trainingProgram?->displayTitle() ?? '-'),
                TD::make('topic_type', tkey('education.learning_topics.fields.topic_type'))
                    ->render(fn (LearningTopic $topic): string => tkey('education.learning_topics.types.'.$topic->topic_type)),
                TD::make('duration_minutes', tkey('education.learning_topics.fields.duration_minutes'))
                    ->render(fn (LearningTopic $topic): string => (string) ($topic->duration_minutes ?? '-')),
            ]),
        ];
    }

    public function save(LearningTopicRequest $request, CreateOrUpdateLearningTopicAction $saveTopic): RedirectResponse
    {
        $topic = filled($request->input('topic.id'))
            ? LearningTopic::query()->findOrFail($request->integer('topic.id'))
            : new LearningTopic;

        $saveTopic->handle($topic, $request->topicData(), $request->user());

        Toast::info(tkey('education.groups.messages.saved'));

        return redirect()->route('platform.education.learning-topics');
    }

    /**
     * @return array<string, string>
     */
    private function topicTypeOptions(): array
    {
        return collect(['theory', 'practice', 'simulator', 'exam_preparation', 'other'])
            ->mapWithKeys(fn (string $type): array => [$type => tkey('education.learning_topics.types.'.$type)])
            ->all();
    }
}
