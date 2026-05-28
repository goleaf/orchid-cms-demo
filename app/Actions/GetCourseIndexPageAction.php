<?php

namespace App\Actions;

use App\Models\Branch;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\SiteSetting;
use App\Models\TrainingGroup;
use App\Support\Website\BranchLocationFilters;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class GetCourseIndexPageAction
{
    public function __construct(private readonly BranchLocationFilters $locationFilters) {}

    /**
     * @return array<string, mixed>
     */
    public function handle(?Request $request = null): array
    {
        $request ??= request();
        $filterBranches = Branch::query()
            ->forAdminList()
            ->active()
            ->visibleOnSite()
            ->ordered()
            ->get();
        $filters = [
            ...$this->locationFilters->normalize($this->locationFilters->fromRequest($request), $filterBranches),
            'category' => trim((string) $request->query('category', '')),
        ];
        $hasLocationFilter = $this->locationFilters->hasActive($filters);
        $locationOptions = $this->locationFilters->options($filterBranches, $filters);

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
                    ->visibleOnSite()
                    ->when($hasLocationFilter, function (Builder $query) use ($filters): Builder {
                        return $query->whereHas('groups', function (Builder $query) use ($filters): void {
                            $query
                                ->openForEnrollment()
                                ->whereHas('branch', fn (Builder $query): Builder => $this->locationFilters->applyPublicLocation($query, $filters));
                        });
                    }),
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
                filled($filters['category']),
                fn ($query) => $query->whereHas(
                    'category',
                    fn ($categoryQuery) => $categoryQuery
                        ->where('slug', $filters['category'])
                        ->active()
                        ->visibleOnSite(),
                ),
            )
            ->when($hasLocationFilter, function (Builder $query) use ($filters): Builder {
                return $query->whereHas('groups', function (Builder $query) use ($filters): void {
                    $query
                        ->openForEnrollment()
                        ->whereHas('branch', fn (Builder $query): Builder => $this->locationFilters->applyPublicLocation($query, $filters));
                });
            })
            ->ordered()
            ->get();

        $groups = TrainingGroup::query()
            ->operationalList()
            ->with([
                'branch:id,name,name_translations,country,country_translations,city,city_translations,is_active,is_visible_on_site',
                'trainingProgram:id,title,title_translations,name_translations,slug,license_category,price_cents,is_active,is_visible_on_site',
            ])
            ->withCount('enrollments')
            ->openForEnrollment()
            ->when(filled($filters['category']), function (Builder $query) use ($filters): Builder {
                return $query->whereHas('trainingProgram.category', fn (Builder $query): Builder => $query->where('slug', $filters['category']));
            })
            ->when($hasLocationFilter, fn (Builder $query): Builder => $query->whereHas('branch', fn (Builder $query): Builder => $this->locationFilters->applyPublicLocation($query, $filters)))
            ->ordered()
            ->limit(12)
            ->get();

        return [
            'categories' => $categories,
            'courses' => $courses,
            'branches' => $this->locationFilters->filterBranches($filterBranches, $filters),
            'groups' => $groups,
            'filters' => $filters,
            'filterOptions' => [
                'countries' => $locationOptions['countries'],
                'cities' => $locationOptions['cities'],
                'categories' => $categories,
            ],
            'selectedCategorySlug' => $filters['category'],
            'hasActiveFilters' => collect($filters)->contains(fn (?string $value): bool => filled($value)),
            'courseContextQuery' => collect([
                'country' => $filters['country'],
                'city' => $filters['city'],
            ])->filter(fn (?string $value): bool => filled($value))->all(),
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
