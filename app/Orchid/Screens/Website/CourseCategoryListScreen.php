<?php

namespace App\Orchid\Screens\Website;

use App\Actions\MoveSortableOrderAction;
use App\Models\CourseCategory;
use App\Orchid\Screens\Website\Concerns\BuildsWebsiteScreenPayloads;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class CourseCategoryListScreen extends Screen
{
    use BuildsWebsiteScreenPayloads;

    public function query(): iterable
    {
        $categories = CourseCategory::query()
            ->select([
                'id',
                'code',
                'slug',
                'name_translations',
                'seo_title_translations',
                'seo_description_translations',
                'is_active',
                'is_visible_on_site',
                'sort_order',
            ])
            ->ordered()
            ->simplePaginate(15);

        $this->applyOrderControlState($categories, CourseCategory::class);

        return [
            'categories' => $categories,
        ];
    }

    public function name(): ?string
    {
        return tkey('website.admin.course_categories.title');
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
            Link::make(tkey('website.admin.actions.create'))
                ->icon('bs.plus-circle')
                ->route('platform.website.course-categories.create'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::table('categories', [
                TD::make('name', tkey('website.admin.course_categories.fields.name'))
                    ->render(fn (CourseCategory $category): string => $category->displayName().' '.$this->seoWarning($category->displaySeoTitle(), $category->displaySeoDescription())),
                TD::make('slug', tkey('website.seo.fields.slug'))
                    ->render(fn (CourseCategory $category): string => $category->slug),
                TD::make('code', tkey('website.admin.fields.code'))
                    ->render(fn (CourseCategory $category): string => $category->code ?? '-'),
                TD::make('is_active', tkey('website.admin.fields.is_active'))
                    ->alignCenter()
                    ->render(fn (CourseCategory $category): string => $this->booleanBadge($category->is_active, 'website.admin.status.active', 'website.admin.status.inactive')),
                TD::make('is_visible_on_site', tkey('website.admin.fields.is_visible_on_site'))
                    ->alignCenter()
                    ->render(fn (CourseCategory $category): string => $this->booleanBadge($category->is_visible_on_site, 'website.admin.status.visible', 'website.admin.status.hidden')),
                TD::make('order_controls', tkey('website.admin.fields.position'))
                    ->alignCenter()
                    ->render(fn (CourseCategory $category): string => $this->orderControls($category)),
                TD::make('actions', tkey('crm.leads.columns.actions'))
                    ->alignRight()
                    ->render(fn (CourseCategory $category): string => (string) Link::make(tkey('common.actions.edit'))
                        ->icon('bs.pencil')
                        ->route('platform.website.course-categories.edit', $category)),
            ]),
        ];
    }

    public function moveUp(Request $request, MoveSortableOrderAction $move): RedirectResponse
    {
        return $this->moveSortable($request, CourseCategory::class, 'platform.website.course-categories', $move, MoveSortableOrderAction::UP);
    }

    public function moveDown(Request $request, MoveSortableOrderAction $move): RedirectResponse
    {
        return $this->moveSortable($request, CourseCategory::class, 'platform.website.course-categories', $move, MoveSortableOrderAction::DOWN);
    }
}
