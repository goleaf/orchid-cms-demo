<?php

namespace App\Orchid\Screens\Website;

use App\Actions\MoveSortableOrderAction;
use App\Enums\CourseFormat;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Orchid\Screens\Website\Concerns\BuildsWebsiteScreenPayloads;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class CourseListScreen extends Screen
{
    use BuildsWebsiteScreenPayloads;

    /**
     * @var array<string, mixed>
     */
    private array $filters = [];

    /**
     * @var array<int, string>
     */
    private array $categories = [];

    public function query(Request $request): iterable
    {
        $this->filters = $request->only(['course_category_id', 'active', 'visible_on_site', 'featured', 'format']);
        $this->categories = CourseCategory::query()
            ->select(['id', 'name_translations', 'code', 'slug', 'sort_order'])
            ->ordered()
            ->limit(100)
            ->get()
            ->mapWithKeys(fn (CourseCategory $category): array => [$category->id => $category->displayName()])
            ->all();

        $courses = Course::query()
            ->select([
                'id',
                'course_category_id',
                'code',
                'slug',
                'title',
                'title_translations',
                'name_translations',
                'license_category',
                'theory_hours',
                'practice_hours',
                'format',
                'price_cents',
                'price',
                'old_price_cents',
                'currency',
                'seo_title_translations',
                'seo_description_translations',
                'is_active',
                'is_visible_on_site',
                'is_featured',
                'sort_order',
            ])
            ->with(['category:id,name_translations,code,slug'])
            ->when(filled($this->filters['course_category_id'] ?? null), fn (Builder $query) => $query->where('course_category_id', $this->filters['course_category_id']))
            ->when(($this->filters['active'] ?? '') !== '', fn (Builder $query) => $query->where('is_active', (bool) $this->filters['active']))
            ->when(($this->filters['visible_on_site'] ?? '') !== '', fn (Builder $query) => $query->where('is_visible_on_site', (bool) $this->filters['visible_on_site']))
            ->when(($this->filters['featured'] ?? '') !== '', fn (Builder $query) => $query->where('is_featured', (bool) $this->filters['featured']))
            ->when(filled($this->filters['format'] ?? null), fn (Builder $query) => $query->where('format', $this->filters['format']))
            ->ordered()
            ->simplePaginate(15)
            ->withQueryString();

        $this->applyOrderControlState($courses, Course::class);

        return [
            'courses' => $courses,
        ];
    }

    public function name(): ?string
    {
        return tkey('website.admin.courses.title');
    }

    public function description(): ?string
    {
        return tkey('website.admin.courses.description');
    }

    public function permission(): iterable
    {
        return ['website.manage_courses'];
    }

    public function commandBar(): iterable
    {
        return [
            Link::make(tkey('website.admin.actions.create'))
                ->icon('bs.plus-circle')
                ->route('platform.website.courses.create'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::rows([
                Group::make([
                    Select::make('course_category_id')
                        ->title(tkey('website.admin.courses.fields.category'))
                        ->empty(tkey('website.admin.filters.all_categories'), '')
                        ->options($this->categories)
                        ->value($this->filters['course_category_id'] ?? ''),
                    Select::make('active')
                        ->title(tkey('website.admin.filters.active'))
                        ->empty(tkey('website.admin.filters.all_statuses'), '')
                        ->options($this->booleanOptions())
                        ->value($this->filters['active'] ?? ''),
                    Select::make('visible_on_site')
                        ->title(tkey('website.admin.filters.visible_on_site'))
                        ->empty(tkey('website.admin.filters.all_visibility'), '')
                        ->options($this->booleanOptions())
                        ->value($this->filters['visible_on_site'] ?? ''),
                    Select::make('featured')
                        ->title(tkey('website.admin.filters.featured'))
                        ->empty(tkey('website.admin.filters.all_featured'), '')
                        ->options($this->booleanOptions())
                        ->value($this->filters['featured'] ?? ''),
                    Select::make('format')
                        ->title(tkey('website.courses.fields.format'))
                        ->empty(tkey('website.admin.filters.all_formats'), '')
                        ->options($this->courseFormatOptions())
                        ->value($this->filters['format'] ?? ''),
                    Button::make(tkey('common.actions.search'))
                        ->icon('bs.search')
                        ->method('filter')
                        ->novalidate(),
                ])
                    ->alignEnd()
                    ->widthColumns('minmax(220px, 1.4fr) repeat(4, minmax(150px, 1fr)) max-content'),
            ]),

            Layout::table('courses', [
                TD::make('name', tkey('website.courses.fields.name'))
                    ->render(fn (Course $course): string => $course->displayTitle().' '.$this->seoWarning($course->displaySeoTitle(), $course->displaySeoDescription())),
                TD::make('category', tkey('website.admin.courses.fields.category'))
                    ->render(fn (Course $course): string => $course->category?->displayName() ?? '-'),
                TD::make('price', tkey('website.courses.fields.price'))
                    ->render(fn (Course $course): string => $course->priceForHumans()),
                TD::make('format', tkey('website.courses.fields.format'))
                    ->render(fn (Course $course): string => tkey('website.courses.formats.'.($course->format ?: CourseFormat::Mixed->value))),
                TD::make('is_active', tkey('website.admin.fields.is_active'))
                    ->alignCenter()
                    ->render(fn (Course $course): string => $this->booleanBadge($course->is_active, 'website.admin.status.active', 'website.admin.status.inactive')),
                TD::make('is_visible_on_site', tkey('website.admin.fields.is_visible_on_site'))
                    ->alignCenter()
                    ->render(fn (Course $course): string => $this->booleanBadge($course->is_visible_on_site, 'website.admin.status.visible', 'website.admin.status.hidden')),
                TD::make('is_featured', tkey('website.admin.fields.is_featured'))
                    ->alignCenter()
                    ->render(fn (Course $course): string => $this->booleanBadge($course->is_featured)),
                TD::make('order_controls', tkey('website.admin.fields.position'))
                    ->alignCenter()
                    ->render(fn (Course $course): string => $this->orderControls($course)),
                TD::make('actions', tkey('crm.leads.columns.actions'))
                    ->alignRight()
                    ->render(fn (Course $course): string => $this->courseActions($course)),
            ]),
        ];
    }

    public function filter(Request $request): RedirectResponse
    {
        return redirect()->route('platform.website.courses', array_filter([
            'course_category_id' => $request->input('course_category_id'),
            'active' => $request->input('active'),
            'visible_on_site' => $request->input('visible_on_site'),
            'featured' => $request->input('featured'),
            'format' => $request->input('format'),
        ], fn (mixed $value): bool => $value !== null && $value !== ''));
    }

    public function moveUp(Request $request, MoveSortableOrderAction $move): RedirectResponse
    {
        return $this->moveSortable($request, Course::class, 'platform.website.courses', $move, MoveSortableOrderAction::UP);
    }

    public function moveDown(Request $request, MoveSortableOrderAction $move): RedirectResponse
    {
        return $this->moveSortable($request, Course::class, 'platform.website.courses', $move, MoveSortableOrderAction::DOWN);
    }

    private function courseActions(Course $course): string
    {
        return implode(' ', [
            (string) Link::make(tkey('common.actions.edit'))
                ->icon('bs.pencil')
                ->route('platform.website.courses.edit', $course),
            (string) Link::make(tkey('website.admin.actions.open_public_page'))
                ->icon('bs.box-arrow-up-right')
                ->route('site.courses.show', $course)
                ->target('_blank'),
        ]);
    }
}
