<?php

declare(strict_types=1);

namespace App\Orchid\Screens\School;

use App\Actions\UpdateLearningProgramAction;
use App\Models\CourseCategory;
use App\Models\LearningProgram;
use App\Models\TrainingProgram;
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

class LearningProgramListScreen extends Screen
{
    /**
     * @var array<string, mixed>
     */
    private array $filters = [];

    /**
     * @var array<int, string>
     */
    private array $courses = [];

    /**
     * @var array<int, string>
     */
    private array $categories = [];

    public function query(Request $request): iterable
    {
        $this->filters = $this->filtersFromRequest($request);
        $this->loadOptions();

        return [
            'programs' => $this->programQuery()
                ->orderBy('sort_order')
                ->orderBy('id')
                ->simplePaginate(20)
                ->withQueryString(),
        ];
    }

    public function name(): ?string
    {
        return tkey('education.programs.title');
    }

    public function permission(): iterable
    {
        return ['education.programs.view'];
    }

    public function commandBar(): iterable
    {
        return [
            Link::make(tkey('education.programs.actions.create'))
                ->icon('bs.plus-circle')
                ->route('platform.education.programs.create')
                ->canSee($this->hasAccess('education.programs.create')),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::rows([
                Input::make('search')
                    ->title(tkey('education.groups.filters.search'))
                    ->value($this->filters['search'] ?? ''),
                Select::make('course_id')
                    ->title(tkey('education.programs.fields.course'))
                    ->empty(tkey('education.groups.segments.all'), '')
                    ->options($this->courses)
                    ->value($this->filters['course_id'] ?? ''),
                Select::make('course_category_id')
                    ->title(tkey('education.programs.fields.course_category'))
                    ->empty(tkey('education.groups.segments.all'), '')
                    ->options($this->categories)
                    ->value($this->filters['course_category_id'] ?? ''),
                Select::make('is_active')
                    ->title(tkey('education.programs.fields.is_active'))
                    ->empty(tkey('education.groups.segments.all'), '')
                    ->options($this->booleanOptions())
                    ->value($this->filters['is_active'] ?? ''),
                Select::make('is_default')
                    ->title(tkey('education.programs.fields.is_default'))
                    ->empty(tkey('education.groups.segments.all'), '')
                    ->options($this->booleanOptions())
                    ->value($this->filters['is_default'] ?? ''),
                Button::make(tkey('common.actions.search'))
                    ->icon('bs.search')
                    ->method('filter')
                    ->novalidate(),
            ])->title(tkey('education.groups.sections.overview')),

            Layout::table('programs', [
                TD::make('name', tkey('education.programs.fields.name'))
                    ->render(fn (LearningProgram $program): string => (string) Link::make($program->display_name)
                        ->route('platform.education.programs.edit', $program)),
                TD::make('code', tkey('education.programs.fields.code'))
                    ->render(fn (LearningProgram $program): string => $program->code ?? '-'),
                TD::make('course', tkey('education.programs.fields.course'))
                    ->render(fn (LearningProgram $program): string => $program->course?->displayTitle() ?? '-'),
                TD::make('course_category', tkey('education.programs.fields.course_category'))
                    ->render(fn (LearningProgram $program): string => $program->courseCategory?->displayName() ?? '-'),
                TD::make('is_default', tkey('education.programs.fields.is_default'))
                    ->render(fn (LearningProgram $program): string => $this->booleanLabel((bool) $program->is_default)),
                TD::make('is_active', tkey('education.programs.fields.is_active'))
                    ->render(fn (LearningProgram $program): string => $this->booleanLabel((bool) $program->is_active)),
                TD::make('modules_count', tkey('education.programs.modules.title'))
                    ->render(fn (LearningProgram $program): string => (string) $program->modules_count)
                    ->alignCenter(),
                TD::make('topics_count', tkey('education.programs.topics.title'))
                    ->render(fn (LearningProgram $program): string => (string) $program->topics_count)
                    ->alignCenter(),
                TD::make('sort_order', tkey('education.programs.fields.sort_order'))
                    ->render(fn (LearningProgram $program): string => (string) $program->sort_order)
                    ->alignCenter(),
                TD::make('actions', tkey('crm.leads.columns.actions'))
                    ->alignRight()
                    ->render(fn (LearningProgram $program): DropDown => DropDown::make()
                        ->icon('bs.three-dots-vertical')
                        ->list([
                            Link::make(tkey('education.programs.actions.open'))
                                ->icon('bs.box-arrow-in-right')
                                ->route('platform.education.programs.edit', $program),
                            Button::make($program->is_active ? tkey('education.programs.actions.deactivate') : tkey('education.programs.actions.activate'))
                                ->icon($program->is_active ? 'bs.pause-circle' : 'bs.play-circle')
                                ->method('toggleActive')
                                ->parameters(['program' => $program->id])
                                ->canSee($this->hasAccess('education.programs.update')),
                            Button::make(tkey('education.programs.actions.set_default'))
                                ->icon('bs.check-circle')
                                ->method('setDefault')
                                ->parameters(['program' => $program->id])
                                ->canSee($this->hasAccess('education.programs.update') && ! $program->is_default),
                        ])),
            ]),
        ];
    }

    public function filter(Request $request): RedirectResponse
    {
        return redirect()->route('platform.education.programs', array_filter([
            'search' => $request->input('search'),
            'course_id' => $request->input('course_id'),
            'course_category_id' => $request->input('course_category_id'),
            'is_active' => $request->input('is_active'),
            'is_default' => $request->input('is_default'),
        ], fn (mixed $value): bool => filled($value)));
    }

    public function toggleActive(Request $request, UpdateLearningProgramAction $updateProgram): RedirectResponse
    {
        abort_unless($request->user()?->hasAccess('education.programs.update'), 403);

        $program = LearningProgram::query()->findOrFail((int) $request->input('program'));
        $updateProgram->handle($program, ['is_active' => ! $program->is_active], $request->user());

        Toast::info(tkey('education.programs.messages.updated'));

        return redirect()->route('platform.education.programs', $request->query());
    }

    public function setDefault(Request $request, UpdateLearningProgramAction $updateProgram): RedirectResponse
    {
        abort_unless($request->user()?->hasAccess('education.programs.update'), 403);

        $program = LearningProgram::query()->findOrFail((int) $request->input('program'));
        $updateProgram->handle($program, ['is_default' => true, 'is_active' => true], $request->user());

        Toast::info(tkey('education.programs.messages.updated'));

        return redirect()->route('platform.education.programs', $request->query());
    }

    private function programQuery(): Builder
    {
        $query = LearningProgram::query()
            ->select([
                'id',
                'course_id',
                'course_category_id',
                'code',
                'name_translations',
                'is_default',
                'is_active',
                'sort_order',
                'created_at',
                'updated_at',
            ])
            ->with([
                'course:id,title,title_translations,name_translations,license_category',
                'courseCategory:id,slug,code,name_translations',
            ])
            ->withCount(['modules', 'topics']);

        $query->search($this->filters['search'] ?? null)
            ->byCourse($this->filters['course_id'] ?? null)
            ->byCourseCategory($this->filters['course_category_id'] ?? null);

        if (filled($this->filters['is_active'] ?? null)) {
            $query->where('is_active', $this->filters['is_active'] === '1');
        }

        if (filled($this->filters['is_default'] ?? null)) {
            $query->where('is_default', $this->filters['is_default'] === '1');
        }

        return $query;
    }

    /**
     * @return array<string, mixed>
     */
    private function filtersFromRequest(Request $request): array
    {
        return collect(['search', 'course_id', 'course_category_id', 'is_active', 'is_default'])
            ->mapWithKeys(fn (string $field): array => [$field => $request->input($field)])
            ->filter(fn (mixed $value): bool => filled($value))
            ->all();
    }

    private function loadOptions(): void
    {
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
    }

    /**
     * @return array<string, string>
     */
    private function booleanOptions(): array
    {
        return [
            '1' => tkey('common.status.yes'),
            '0' => tkey('common.status.no'),
        ];
    }

    private function booleanLabel(bool $value): string
    {
        return $value ? tkey('common.status.yes') : tkey('common.status.no');
    }

    private function hasAccess(string $permission): bool
    {
        return request()->user()?->hasAccess($permission) ?? false;
    }
}
