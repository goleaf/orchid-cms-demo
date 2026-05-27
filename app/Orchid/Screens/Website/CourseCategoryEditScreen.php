<?php

namespace App\Orchid\Screens\Website;

use App\Actions\CreateOrUpdateCourseCategoryAction;
use App\Http\Requests\StoreCourseCategoryRequest;
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

class CourseCategoryEditScreen extends Screen
{
    use BuildsWebsiteScreenPayloads;

    public ?CourseCategory $category = null;

    public function query(?CourseCategory $category = null): iterable
    {
        $categoryModel = $category?->exists
            ? $category
            : new CourseCategory([
                'slug' => '',
                'is_active' => true,
                'is_visible_on_site' => true,
                'sort_order' => 0,
            ]);

        $this->category = $categoryModel;

        return [
            'category' => $categoryModel,
            'id' => $categoryModel->id,
            'code' => $categoryModel->code,
            'slug' => $categoryModel->slug,
            'image' => $categoryModel->image,
            'icon' => $categoryModel->icon,
            'is_active' => $categoryModel->is_active,
            'is_visible_on_site' => $categoryModel->is_visible_on_site,
            'sort_order' => $categoryModel->sort_order,
            'name_translations' => $this->translations($categoryModel, 'name'),
            'short_description_translations' => $this->translations($categoryModel, 'short_description'),
            'description_translations' => $this->translations($categoryModel, 'description'),
            'seo_title_translations' => $this->translations($categoryModel, 'seo_title'),
            'seo_description_translations' => $this->translations($categoryModel, 'seo_description'),
        ];
    }

    public function name(): ?string
    {
        return $this->category?->exists
            ? tkey('website.admin.course_categories.edit_title')
            : tkey('website.admin.course_categories.create_title');
    }

    public function description(): ?string
    {
        return tkey('website.admin.course_categories.description');
    }

    public function permission(): iterable
    {
        return ['website.manage_course_categories', 'website.manage_courses'];
    }

    public function commandBar(): iterable
    {
        return [
            Link::make(tkey('common.actions.back'))
                ->icon('bs.arrow-left')
                ->route('platform.website.course-categories'),
            Button::make(tkey('website.admin.actions.save'))
                ->icon('bs.check-lg')
                ->method('save'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::rows([
                Input::make('id')->type('hidden'),
                Input::make('code')
                    ->title(tkey('website.admin.fields.code')),
                Input::make('slug')
                    ->title(tkey('website.seo.fields.slug'))
                    ->required(),
                Input::make('image')
                    ->title(tkey('website.admin.fields.image_path')),
                Input::make('icon')
                    ->title(tkey('website.admin.fields.icon')),
                Select::make('is_active')
                    ->title(tkey('website.admin.fields.is_active'))
                    ->options($this->booleanOptions()),
                Select::make('is_visible_on_site')
                    ->title(tkey('website.admin.fields.is_visible_on_site'))
                    ->options($this->booleanOptions()),
                Input::make('sort_order')
                    ->type('number')
                    ->title(tkey('website.admin.fields.sort_order')),
            ])->title(tkey('website.admin.sections.main')),

            TranslatableFields::input('name', 'website.admin.course_categories.fields.name', [
                'title_key' => 'website.admin.sections.content',
                'required' => true,
                'maxlength' => 255,
            ]),
            TranslatableFields::textarea('short_description', 'website.admin.course_categories.fields.short_description', [
                'title_key' => 'website.admin.sections.content',
                'rows' => 3,
            ]),
            TranslatableFields::textarea('description', 'website.admin.course_categories.fields.description', [
                'title_key' => 'website.admin.sections.content',
                'rows' => 6,
            ]),
            TranslatableFields::seo([
                'title_key' => 'website.admin.sections.seo',
                'keywords' => false,
            ]),
        ];
    }

    public function save(StoreCourseCategoryRequest $request, CreateOrUpdateCourseCategoryAction $save): RedirectResponse
    {
        $category = $this->resolveScreenModel($request, 'category', CourseCategory::class);

        $save->handle($category, $this->validatedPayload($request, [
            'name',
            'short_description',
            'description',
            'seo_title',
            'seo_description',
        ]));

        Toast::info(tkey('website.admin.course_categories.messages.saved'));

        return redirect()->route('platform.website.course-categories');
    }
}
