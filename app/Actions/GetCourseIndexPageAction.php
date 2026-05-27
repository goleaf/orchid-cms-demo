<?php

namespace App\Actions;

use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\Branch;
use App\Models\SiteSetting;
use App\Models\TrainingGroup;

class GetCourseIndexPageAction
{
    /**
     * @return array<string, mixed>
     */
    public function handle(?string $categorySlug = null): array
    {
        $categories = CourseCategory::query()
            ->select([
                'id',
                'code',
                'slug',
                'name_translations',
                'description_translations',
                'short_description_translations',
                'is_active',
                'is_visible_on_site',
                'sort_order',
            ])
            ->withCount([
                'courses' => fn ($query) => $query
                    ->active()
                    ->visibleOnSite(),
            ])
            ->active()
            ->visibleOnSite()
            ->ordered()
            ->get();

        $courses = Course::query()
            ->forAcademyList()
            ->addSelect([
                'course_category_id',
                'name_translations',
                'duration_translations',
                'price',
                'old_price',
                'currency',
                'is_visible_on_site',
                'is_featured',
            ])
            ->with([
                'category:id,code,slug,name_translations',
            ])
            ->withCount('groups')
            ->active()
            ->visibleOnSite()
            ->when(
                filled($categorySlug),
                fn ($query) => $query->whereHas(
                    'category',
                    fn ($categoryQuery) => $categoryQuery
                        ->where('slug', $categorySlug)
                        ->active()
                        ->visibleOnSite(),
                ),
            )
            ->ordered()
            ->get();

        $groups = TrainingGroup::query()
            ->operationalList()
            ->with([
                'branch:id,name,name_translations,city,city_translations,is_active,is_visible_on_site',
                'trainingProgram:id,title,title_translations,name_translations,slug,license_category,price_cents,is_active,is_visible_on_site',
            ])
            ->withCount('enrollments')
            ->openForEnrollment()
            ->ordered()
            ->limit(12)
            ->get();

        return [
            'categories' => $categories,
            'courses' => $courses,
            'branches' => Branch::query()
                ->forAdminList()
                ->active()
                ->visibleOnSite()
                ->ordered()
                ->get(),
            'groups' => $groups,
            'selectedCategorySlug' => $categorySlug,
            'settings' => SiteSetting::query()
                ->public()
                ->get(['key', 'value'])
                ->mapWithKeys(fn (SiteSetting $setting): array => [$setting->key => $setting->value])
                ->all(),
            'seoTitle' => tkey('website.courses.title').' | '.tkey('website.brand.name'),
            'seoDescription' => tkey('website.home.courses_subtitle'),
            'canonical' => route('website.courses.index'),
        ];
    }
}
