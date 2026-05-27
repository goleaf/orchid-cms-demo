<?php

namespace App\Orchid\Screens\Website;

use App\Actions\CreateOrUpdateCourseAction;
use App\Actions\HideCourseFromSiteAction;
use App\Actions\PublishCourseOnSiteAction;
use App\Http\Requests\StoreCourseRequest;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Orchid\Screens\Website\Concerns\BuildsWebsiteScreenPayloads;
use App\Orchid\Support\TranslatableFields;
use Illuminate\Http\RedirectResponse;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class CourseEditScreen extends Screen
{
    use BuildsWebsiteScreenPayloads;

    public ?Course $course = null;

    /**
     * @var array<int, string>
     */
    private array $categories = [];

    public function query(?Course $program = null): iterable
    {
        $courseModel = $program?->exists
            ? $program
            : new Course([
                'slug' => '',
                'license_category' => 'B',
                'transmission' => 'manual',
                'theory_hours' => 0,
                'practice_hours' => 0,
                'duration_weeks' => 8,
                'format' => 'mixed',
                'price_cents' => 0,
                'currency' => 'EUR',
                'is_active' => true,
                'is_visible_on_site' => true,
                'is_indexable' => true,
                'is_featured' => false,
                'sort_order' => 0,
            ]);

        $this->course = $courseModel;
        $this->categories = CourseCategory::query()
            ->select(['id', 'name_translations', 'code', 'slug', 'sort_order'])
            ->active()
            ->ordered()
            ->limit(100)
            ->get()
            ->mapWithKeys(fn (CourseCategory $category): array => [$category->id => $category->displayName()])
            ->all();

        return [
            'course' => $courseModel,
            'id' => $courseModel->id,
            'course_category_id' => $courseModel->course_category_id,
            'code' => $courseModel->code,
            'slug' => $courseModel->slug,
            'license_category' => $courseModel->license_category,
            'transmission' => $courseModel->transmission,
            'price' => $this->moneyFromCourse($courseModel, 'price'),
            'old_price' => $this->moneyFromCourse($courseModel, 'old_price'),
            'currency' => $courseModel->currency ?: 'EUR',
            'theory_hours' => $courseModel->theory_hours,
            'practice_hours' => $courseModel->practice_hours,
            'duration_weeks' => $courseModel->duration_weeks,
            'format' => $courseModel->format ?: 'mixed',
            'image' => $courseModel->image_path,
            'icon' => $courseModel->icon,
            'og_image' => $courseModel->og_image,
            'canonical_url' => $courseModel->canonical_url,
            'is_active' => $courseModel->is_active,
            'is_visible_on_site' => $courseModel->is_visible_on_site,
            'is_indexable' => $courseModel->is_indexable,
            'is_featured' => $courseModel->is_featured,
            'sort_order' => $courseModel->sort_order,
            'name_translations' => $this->translations($courseModel, 'name', $courseModel->title) ?: $this->translations($courseModel, 'title', $courseModel->title),
            'short_description_translations' => $this->translations($courseModel, 'short_description', $courseModel->short_description),
            'description_translations' => $this->translations($courseModel, 'description', $courseModel->description),
            'program_summary_translations' => $this->translations($courseModel, 'program_summary'),
            'includes_translations' => $this->translations($courseModel, 'includes', $courseModel->included_items),
            'excludes_translations' => $this->translations($courseModel, 'excludes', $courseModel->extra_costs),
            'requirements_translations' => $this->translations($courseModel, 'requirements', $courseModel->admission_requirements),
            'duration_translations' => $this->translations($courseModel, 'duration'),
            'seo_title_translations' => $this->translations($courseModel, 'seo_title', $courseModel->seo_title),
            'seo_description_translations' => $this->translations($courseModel, 'seo_description', $courseModel->meta_description),
            'og_title_translations' => $this->translations($courseModel, 'og_title', $courseModel->og_title),
            'og_description_translations' => $this->translations($courseModel, 'og_description', $courseModel->og_description),
        ];
    }

    public function name(): ?string
    {
        return $this->course?->exists
            ? tkey('website.admin.courses.edit_title')
            : tkey('website.admin.courses.create_title');
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
            Link::make(tkey('common.actions.back'))
                ->icon('bs.arrow-left')
                ->route('platform.website.courses'),
            Link::make(tkey('website.admin.actions.preview'))
                ->icon('bs.box-arrow-up-right')
                ->href($this->course?->exists ? route('website.courses.show', $this->course) : '#')
                ->target('_blank')
                ->canSee((bool) $this->course?->exists),
            Button::make(tkey('website.admin.actions.save'))
                ->icon('bs.check-lg')
                ->method('save'),
            Button::make(tkey('website.admin.actions.publish'))
                ->icon('bs.upload')
                ->method('publish')
                ->canSee((bool) $this->course?->exists),
            Button::make(tkey('website.admin.actions.hide'))
                ->icon('bs.eye-slash')
                ->method('hide')
                ->canSee((bool) $this->course?->exists),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::rows([
                Input::make('id')->type('hidden'),
                Select::make('course_category_id')
                    ->title(tkey('website.admin.courses.fields.category'))
                    ->empty(tkey('website.admin.filters.no_category'), '')
                    ->options($this->categories),
                Input::make('code')
                    ->title(tkey('website.admin.fields.code')),
                Input::make('slug')
                    ->title(tkey('website.seo.fields.slug'))
                    ->required(),
                Input::make('license_category')
                    ->title(tkey('website.admin.courses.fields.license_category'))
                    ->required(),
                Select::make('transmission')
                    ->title(tkey('website.admin.courses.fields.transmission'))
                    ->options([
                        'manual' => tkey('website.transmissions.manual'),
                        'automatic' => tkey('website.transmissions.automatic'),
                    ])
                    ->required(),
                Input::make('sort_order')
                    ->type('number')
                    ->title(tkey('website.admin.fields.sort_order')),
            ])->title(tkey('website.admin.sections.main')),

            TranslatableFields::input('name', 'website.courses.fields.name', [
                'title_key' => 'website.admin.sections.content',
                'required' => true,
                'maxlength' => 255,
            ]),
            TranslatableFields::textarea('short_description', 'website.courses.fields.short_description', [
                'title_key' => 'website.admin.sections.content',
                'rows' => 3,
            ]),
            TranslatableFields::textarea('description', 'website.courses.fields.description', [
                'title_key' => 'website.admin.sections.content',
                'rows' => 6,
            ]),
            TranslatableFields::textarea('program_summary', 'website.courses.fields.program_summary', [
                'title_key' => 'website.admin.sections.curriculum',
                'rows' => 4,
            ]),
            TranslatableFields::textarea('includes', 'website.courses.fields.includes', [
                'title_key' => 'website.admin.sections.price',
                'rows' => 4,
            ]),
            TranslatableFields::textarea('excludes', 'website.courses.fields.excludes', [
                'title_key' => 'website.admin.sections.price',
                'rows' => 4,
            ]),
            TranslatableFields::textarea('requirements', 'website.courses.fields.requirements', [
                'title_key' => 'website.admin.sections.content',
                'rows' => 4,
            ]),
            TranslatableFields::input('duration', 'website.courses.fields.duration', [
                'title_key' => 'website.admin.sections.price',
                'maxlength' => 255,
            ]),

            Layout::rows([
                Input::make('price')
                    ->type('number')
                    ->step('0.01')
                    ->title(tkey('website.courses.fields.price')),
                Input::make('old_price')
                    ->type('number')
                    ->step('0.01')
                    ->title(tkey('website.courses.fields.old_price')),
                Input::make('currency')
                    ->title(tkey('website.admin.pricing.fields.currency'))
                    ->maxlength(3),
                Input::make('theory_hours')
                    ->type('number')
                    ->step('0.25')
                    ->title(tkey('website.courses.fields.theory_hours')),
                Input::make('practice_hours')
                    ->type('number')
                    ->step('0.25')
                    ->title(tkey('website.courses.fields.practice_hours')),
                Input::make('duration_weeks')
                    ->type('number')
                    ->title(tkey('website.admin.courses.fields.duration_weeks')),
                Select::make('format')
                    ->title(tkey('website.courses.fields.format'))
                    ->options($this->courseFormatOptions()),
            ])->title(tkey('website.admin.sections.price')),

            Layout::rows([
                Input::make('image')
                    ->title(tkey('website.admin.fields.image_path')),
                Input::make('icon')
                    ->title(tkey('website.admin.fields.icon')),
                Input::make('og_image')
                    ->title(tkey('website.seo.fields.og_image')),
                Input::make('canonical_url')
                    ->title(tkey('website.seo.fields.canonical_url')),
                Select::make('is_active')
                    ->title(tkey('website.admin.fields.is_active'))
                    ->options($this->booleanOptions()),
                Select::make('is_visible_on_site')
                    ->title(tkey('website.admin.fields.is_visible_on_site'))
                    ->options($this->booleanOptions()),
                Select::make('is_indexable')
                    ->title(tkey('website.seo.fields.is_indexable'))
                    ->options($this->booleanOptions()),
                Select::make('is_featured')
                    ->title(tkey('website.admin.fields.is_featured'))
                    ->options($this->booleanOptions()),
            ])->title(tkey('website.admin.sections.system')),

            TranslatableFields::seo([
                'title_key' => 'website.admin.sections.seo',
                'keywords' => false,
            ]),
        ];
    }

    public function save(StoreCourseRequest $request, CreateOrUpdateCourseAction $save): RedirectResponse
    {
        $course = $this->resolveScreenModel($request, 'program', Course::class, 'slug')
            ?? $this->resolveScreenModel($request, 'course', Course::class, 'slug');

        $save->handle($course, $this->validatedPayload($request, [
            'name',
            'short_description',
            'description',
            'program_summary',
            'includes',
            'excludes',
            'requirements',
            'duration',
            'seo_title',
            'seo_description',
            'og_title',
            'og_description',
        ]));

        Toast::info(tkey('website.admin.courses.messages.saved'));

        return redirect()->route('platform.website.courses');
    }

    public function publish(PublishCourseOnSiteAction $publish): RedirectResponse
    {
        $course = $this->course?->exists ? $this->course : $this->resolveScreenModel(request(), 'program', Course::class, 'slug');

        if ($course instanceof Course && $course->exists) {
            $publish->handle($course);
            Toast::info(tkey('website.admin.courses.messages.published'));
        }

        return redirect()->route('platform.website.courses');
    }

    public function hide(HideCourseFromSiteAction $hide): RedirectResponse
    {
        $course = $this->course?->exists ? $this->course : $this->resolveScreenModel(request(), 'program', Course::class, 'slug');

        if ($course instanceof Course && $course->exists) {
            $hide->handle($course);
            Toast::info(tkey('website.admin.courses.messages.hidden'));
        }

        return redirect()->route('platform.website.courses');
    }

    private function moneyFromCourse(Course $course, string $field): ?string
    {
        $decimal = $course->{$field};

        if ($decimal !== null) {
            return number_format((float) $decimal, 2, '.', '');
        }

        $cents = $field === 'old_price'
            ? $course->old_price_cents
            : $course->price_cents;

        return $cents !== null ? number_format($cents / 100, 2, '.', '') : null;
    }
}
