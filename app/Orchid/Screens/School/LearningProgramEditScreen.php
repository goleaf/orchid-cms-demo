<?php

declare(strict_types=1);

namespace App\Orchid\Screens\School;

use App\Actions\CreateLearningProgramAction;
use App\Actions\CreateLearningProgramModuleAction;
use App\Actions\CreateLearningTopicAction;
use App\Actions\UpdateLearningProgramAction;
use App\Actions\UpdateLearningProgramModuleAction;
use App\Actions\UpdateLearningTopicAction;
use App\Http\Requests\Education\StoreLearningProgramModuleRequest;
use App\Http\Requests\Education\StoreLearningProgramRequest;
use App\Http\Requests\Education\StoreLearningTopicRequest;
use App\Models\CourseCategory;
use App\Models\LearningProgram;
use App\Models\LearningProgramModule;
use App\Models\LearningTopic;
use App\Models\TrainingProgram;
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
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class LearningProgramEditScreen extends Screen
{
    public ?LearningProgram $program = null;

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
    private array $modules = [];

    public function query(?LearningProgram $program = null): iterable
    {
        $programModel = $program?->exists
            ? $program->loadMissing([
                'course:id,title,title_translations,name_translations,license_category',
                'courseCategory:id,slug,code,name_translations',
                'modules.topics',
                'creator:id,name',
                'updater:id,name',
            ])
            : new LearningProgram([
                'is_active' => true,
                'is_default' => false,
                'sort_order' => 0,
            ]);

        $this->program = $programModel;
        $this->authorizeScreen($programModel);
        $this->loadOptions($programModel);

        $topics = ($programModel->modules ?? collect())
            ->flatMap(function (LearningProgramModule $module) {
                return $module->topics->map(function (LearningTopic $topic) use ($module): LearningTopic {
                    $topic->setRelation('module', $module);

                    return $topic;
                });
            })
            ->sortBy('sort_order')
            ->values();

        return [
            'program' => $programModel,
            'program.name_translations' => $programModel->name_translations ?? [],
            'program.description_translations' => $programModel->description_translations ?? [],
            'module.learning_program_id' => $programModel->id,
            'topic.learning_program_module_id' => null,
            'modules' => $programModel->modules?->sortBy('sort_order')->values() ?? collect(),
            'topics' => $topics,
        ];
    }

    public function name(): ?string
    {
        return $this->program?->exists
            ? tkey('education.programs.edit_title')
            : tkey('education.programs.create_title');
    }

    public function permission(): iterable
    {
        return ['education.programs.create', 'education.programs.update'];
    }

    public function commandBar(): iterable
    {
        return [
            Link::make(tkey('common.actions.back'))
                ->icon('bs.arrow-left')
                ->route('platform.education.programs'),

            Button::make(tkey('education.programs.actions.save'))
                ->icon('bs.check2-circle')
                ->method('save'),

            ModalToggle::make(tkey('education.programs.actions.add_module'))
                ->icon('bs.plus-square')
                ->modal('moduleModal')
                ->canSee($this->program?->exists && $this->hasAccess('education.programs.manage_modules')),

            ModalToggle::make(tkey('education.programs.actions.add_topic'))
                ->icon('bs.plus-circle')
                ->modal('topicModal')
                ->canSee($this->program?->exists && $this->hasAccess('education.programs.manage_topics') && $this->modules !== []),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::rows([
                Input::make('program.id')->type('hidden'),
                Input::make('program.code')
                    ->title(tkey('education.programs.fields.code')),
                Select::make('program.course_id')
                    ->title(tkey('education.programs.fields.course'))
                    ->options($this->courses)
                    ->empty(tkey('education.groups.segments.all'), ''),
                Select::make('program.course_category_id')
                    ->title(tkey('education.programs.fields.course_category'))
                    ->options($this->categories)
                    ->empty(tkey('education.groups.segments.all'), ''),
                Switcher::make('program.is_default')
                    ->sendTrueOrFalse()
                    ->title(tkey('education.programs.fields.is_default')),
                Switcher::make('program.is_active')
                    ->sendTrueOrFalse()
                    ->title(tkey('education.programs.fields.is_active')),
                Input::make('program.sort_order')
                    ->type('number')
                    ->min(0)
                    ->title(tkey('education.programs.fields.sort_order')),
            ])->title(tkey('education.groups.sections.main_information')),

            TranslatableFields::input('program.name', 'education.programs.fields.name', [
                'title_key' => 'education.groups.sections.translated_content',
                'maxlength' => 255,
                'required' => true,
            ]),

            TranslatableFields::textarea('program.description', 'education.programs.fields.description', [
                'title_key' => 'education.groups.sections.translated_content',
                'rows' => 3,
                'maxlength' => 2000,
            ]),

            Layout::table('modules', [
                TD::make('name', tkey('education.programs.modules.fields.name'))
                    ->render(fn (LearningProgramModule $module): string => $module->display_name),
                TD::make('code', tkey('education.programs.modules.fields.code'))
                    ->render(fn (LearningProgramModule $module): string => $module->code ?? '-'),
                TD::make('type', tkey('education.programs.modules.fields.type'))
                    ->render(fn (LearningProgramModule $module): string => tkey('education.programs.modules.types.'.$module->type)),
                TD::make('required_hours', tkey('education.programs.modules.fields.required_hours'))
                    ->render(fn (LearningProgramModule $module): string => (string) ($module->required_hours ?? '-')),
                TD::make('sort_order', tkey('education.programs.modules.fields.sort_order'))
                    ->render(fn (LearningProgramModule $module): string => (string) $module->sort_order),
                TD::make('is_active', tkey('education.programs.modules.fields.is_active'))
                    ->render(fn (LearningProgramModule $module): string => $module->is_active ? tkey('common.status.yes') : tkey('common.status.no')),
                TD::make('actions', tkey('crm.leads.columns.actions'))
                    ->alignRight()
                    ->render(fn (LearningProgramModule $module): DropDown => DropDown::make()
                        ->icon('bs.three-dots-vertical')
                        ->list([
                            Button::make($module->is_active ? tkey('education.programs.actions.deactivate') : tkey('education.programs.actions.activate'))
                                ->icon($module->is_active ? 'bs.pause-circle' : 'bs.play-circle')
                                ->method('toggleModule')
                                ->parameters(['module_id' => $module->id])
                                ->canSee($this->hasAccess('education.programs.manage_modules')),
                        ])),
            ])->title(tkey('education.programs.modules.title')),

            Layout::table('topics', [
                TD::make('name', tkey('education.programs.topics.fields.name'))
                    ->render(fn (LearningTopic $topic): string => $topic->display_name),
                TD::make('module', tkey('education.programs.modules.title'))
                    ->render(fn (LearningTopic $topic): string => $topic->module?->display_name ?? '-'),
                TD::make('code', tkey('education.programs.topics.fields.code'))
                    ->render(fn (LearningTopic $topic): string => $topic->code ?? '-'),
                TD::make('estimated_hours', tkey('education.programs.topics.fields.estimated_hours'))
                    ->render(fn (LearningTopic $topic): string => (string) ($topic->estimated_hours ?? '-')),
                TD::make('sort_order', tkey('education.programs.topics.fields.sort_order'))
                    ->render(fn (LearningTopic $topic): string => (string) $topic->sort_order),
                TD::make('is_active', tkey('education.programs.topics.fields.is_active'))
                    ->render(fn (LearningTopic $topic): string => $topic->is_active ? tkey('common.status.yes') : tkey('common.status.no')),
                TD::make('actions', tkey('crm.leads.columns.actions'))
                    ->alignRight()
                    ->render(fn (LearningTopic $topic): DropDown => DropDown::make()
                        ->icon('bs.three-dots-vertical')
                        ->list([
                            Button::make($topic->is_active ? tkey('education.programs.actions.deactivate') : tkey('education.programs.actions.activate'))
                                ->icon($topic->is_active ? 'bs.pause-circle' : 'bs.play-circle')
                                ->method('toggleTopic')
                                ->parameters(['topic_id' => $topic->id])
                                ->canSee($this->hasAccess('education.programs.manage_topics')),
                        ])),
            ])->title(tkey('education.programs.topics.title')),

            ...$this->modals(),
        ];
    }

    public function save(
        StoreLearningProgramRequest $request,
        CreateLearningProgramAction $createProgram,
        UpdateLearningProgramAction $updateProgram,
    ): RedirectResponse {
        if (filled($request->input('program.id'))) {
            $program = LearningProgram::query()->findOrFail((int) $request->input('program.id'));
            $program = $updateProgram->handle($program, $request->programData(), $request->user());
        } else {
            $program = $createProgram->handle($request->programData(), $request->user());
        }

        Toast::info(tkey('education.programs.messages.updated'));

        return redirect()->route('platform.education.programs.edit', $program);
    }

    public function createModule(
        StoreLearningProgramModuleRequest $request,
        CreateLearningProgramModuleAction $createModule,
    ): RedirectResponse {
        $module = $createModule->handle($request->moduleData(), $request->user());

        Toast::info(tkey('education.programs.messages.module_created'));

        return redirect()->route('platform.education.programs.edit', $module->learning_program_id);
    }

    public function createTopic(StoreLearningTopicRequest $request, CreateLearningTopicAction $createTopic): RedirectResponse
    {
        $topic = $createTopic->handle($request->topicData(), $request->user());

        Toast::info(tkey('education.programs.messages.topic_created'));

        return redirect()->route('platform.education.programs.edit', $topic->module?->learning_program_id);
    }

    public function toggleModule(Request $request, UpdateLearningProgramModuleAction $updateModule): RedirectResponse
    {
        abort_unless($request->user()?->hasAccess('education.programs.manage_modules'), 403);

        $module = LearningProgramModule::query()->findOrFail((int) $request->input('module_id'));
        $updateModule->handle($module, ['is_active' => ! $module->is_active], $request->user());

        Toast::info(tkey('education.programs.messages.updated'));

        return redirect()->route('platform.education.programs.edit', $module->learning_program_id);
    }

    public function toggleTopic(Request $request, UpdateLearningTopicAction $updateTopic): RedirectResponse
    {
        abort_unless($request->user()?->hasAccess('education.programs.manage_topics'), 403);

        $topic = LearningTopic::query()->findOrFail((int) $request->input('topic_id'));
        $programId = $topic->module?->learning_program_id;
        $updateTopic->handle($topic, ['is_active' => ! $topic->is_active], $request->user());

        Toast::info(tkey('education.programs.messages.updated'));

        return redirect()->route('platform.education.programs.edit', $programId);
    }

    private function authorizeScreen(LearningProgram $program): void
    {
        $permission = $program->exists ? 'education.programs.update' : 'education.programs.create';

        abort_unless(request()->user()?->hasAccess($permission), 403);
    }

    private function loadOptions(LearningProgram $program): void
    {
        $this->courses = TrainingProgram::query()
            ->forAcademyList()
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get(['id', 'title', 'title_translations', 'name_translations', 'license_category', 'sort_order'])
            ->mapWithKeys(fn (TrainingProgram $course): array => [$course->id => $course->displayTitle()])
            ->all();

        $this->categories = CourseCategory::query()
            ->active()
            ->ordered()
            ->get(['id', 'slug', 'code', 'name_translations'])
            ->mapWithKeys(fn (CourseCategory $category): array => [$category->id => $category->displayName()])
            ->all();

        $this->modules = $program->exists
            ? $program->modules()
                ->forProgramOutline()
                ->get()
                ->mapWithKeys(fn (LearningProgramModule $module): array => [$module->id => $module->display_name])
                ->all()
            : [];
    }

    /**
     * @return array<int, mixed>
     */
    private function modals(): array
    {
        return [
            Layout::modal('moduleModal', [
                Layout::rows([
                    Input::make('module.learning_program_id')->type('hidden'),
                    Input::make('module.code')
                        ->title(tkey('education.programs.modules.fields.code')),
                    Select::make('module.type')
                        ->title(tkey('education.programs.modules.fields.type'))
                        ->options($this->moduleTypes())
                        ->required(),
                    Input::make('module.required_hours')
                        ->type('number')
                        ->step('0.25')
                        ->title(tkey('education.programs.modules.fields.required_hours')),
                    Input::make('module.sort_order')
                        ->type('number')
                        ->title(tkey('education.programs.modules.fields.sort_order')),
                    Switcher::make('module.is_required')
                        ->sendTrueOrFalse()
                        ->title(tkey('education.programs.modules.fields.is_required')),
                    Switcher::make('module.is_active')
                        ->sendTrueOrFalse()
                        ->title(tkey('education.programs.modules.fields.is_active')),
                ]),
                TranslatableFields::input('module.name', 'education.programs.modules.fields.name', [
                    'maxlength' => 255,
                    'required' => true,
                ]),
                TranslatableFields::textarea('module.description', 'education.programs.modules.fields.description', [
                    'rows' => 3,
                    'maxlength' => 2000,
                ]),
            ])
                ->title(tkey('education.programs.actions.add_module'))
                ->method('createModule')
                ->applyButton(tkey('education.programs.actions.add_module'))
                ->canSee($this->program?->exists ?? false),

            Layout::modal('topicModal', [
                Layout::rows([
                    Select::make('topic.learning_program_module_id')
                        ->title(tkey('education.programs.modules.title'))
                        ->options($this->modules)
                        ->required(),
                    Input::make('topic.code')
                        ->title(tkey('education.programs.topics.fields.code')),
                    Input::make('topic.topic_type')
                        ->title(tkey('education.programs.modules.fields.type')),
                    Input::make('topic.estimated_hours')
                        ->type('number')
                        ->step('0.25')
                        ->title(tkey('education.programs.topics.fields.estimated_hours')),
                    Input::make('topic.sort_order')
                        ->type('number')
                        ->title(tkey('education.programs.topics.fields.sort_order')),
                    Switcher::make('topic.is_required')
                        ->sendTrueOrFalse()
                        ->title(tkey('education.programs.topics.fields.is_required')),
                    Switcher::make('topic.is_active')
                        ->sendTrueOrFalse()
                        ->title(tkey('education.programs.topics.fields.is_active')),
                ]),
                TranslatableFields::input('topic.name', 'education.programs.topics.fields.name', [
                    'maxlength' => 255,
                    'required' => true,
                ]),
                TranslatableFields::textarea('topic.description', 'education.programs.topics.fields.description', [
                    'rows' => 3,
                    'maxlength' => 2000,
                ]),
            ])
                ->title(tkey('education.programs.actions.add_topic'))
                ->method('createTopic')
                ->applyButton(tkey('education.programs.actions.add_topic'))
                ->canSee($this->program?->exists ?? false),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function moduleTypes(): array
    {
        return [
            'theory' => tkey('education.programs.modules.types.theory'),
            'practice' => tkey('education.programs.modules.types.practice'),
            'exam_preparation' => tkey('education.programs.modules.types.exam_preparation'),
            'internal_exam' => tkey('education.programs.modules.types.internal_exam'),
            'state_exam_preparation' => tkey('education.programs.modules.types.state_exam_preparation'),
            'documents' => tkey('education.programs.modules.types.documents'),
            'onboarding' => tkey('education.programs.modules.types.onboarding'),
            'other' => tkey('education.programs.modules.types.other'),
        ];
    }

    private function hasAccess(string $permission): bool
    {
        return request()->user()?->hasAccess($permission) ?? false;
    }
}
