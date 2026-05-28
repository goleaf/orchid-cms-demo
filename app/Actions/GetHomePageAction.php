<?php

namespace App\Actions;

use App\Enums\StudentStatus;
use App\Models\Branch;
use App\Models\CourseCategory;
use App\Models\Instructor;
use App\Models\LandingPage;
use App\Models\PricingPackage;
use App\Models\SitePage;
use App\Models\SiteSetting;
use App\Models\StudentProfile;
use App\Models\Testimonial;
use App\Models\TrainingGroup;
use App\Models\TrainingProgram;
use App\Models\Vehicle;
use App\Support\Website\BranchLocationFilters;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class GetHomePageAction
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
        $filters = $this->filtersFromRequest($request, $filterBranches);
        $selectedCategory = $this->selectedCategory($filters);
        $categoryId = $selectedCategory?->id;
        $locationOptions = $this->locationFilters->options($filterBranches, $filters);
        $programs = TrainingProgram::query()
            ->forAcademyList()
            ->addSelect(['name_translations', 'is_visible_on_site', 'is_featured'])
            ->active()
            ->visibleOnSite()
            ->when($categoryId !== null, fn (Builder $query): Builder => $query->where('course_category_id', $categoryId))
            ->when($this->locationFilters->hasActive($filters), function (Builder $query) use ($filters): Builder {
                return $query->whereHas('groups', function (Builder $query) use ($filters): void {
                    $query
                        ->openForEnrollment()
                        ->whereHas('branch', fn (Builder $query): Builder => $this->locationFilters->applyPublicLocation($query, $filters));
                });
            })
            ->withCount('groups')
            ->orderBy('sort_order')
            ->orderBy('license_category')
            ->orderBy('title')
            ->limit(12)
            ->get();
        $upcomingGroups = TrainingGroup::query()
            ->operationalList()
            ->with([
                'branch:id,name,name_translations,country,country_translations,city,city_translations',
                'trainingProgram:id,title,title_translations,slug,license_category,price_cents',
                'instructor:id,name',
            ])
            ->withCount('enrollments')
            ->openForEnrollment()
            ->when($categoryId !== null, fn (Builder $query): Builder => $this->applyCategoryToGroup($query, $categoryId))
            ->when($this->locationFilters->hasActive($filters), function (Builder $query) use ($filters): Builder {
                return $query->whereHas('branch', fn (Builder $query): Builder => $this->locationFilters->applyPublicLocation($query, $filters));
            })
            ->orderBy('starts_on')
            ->limit(6)
            ->get();
        $branches = Branch::query()
            ->forAdminList()
            ->withCount(['instructors', 'vehicles', 'groups'])
            ->active()
            ->visibleOnSite()
            ->when($this->locationFilters->hasActive($filters), fn (Builder $query): Builder => $this->locationFilters->applyLocation($query, $filters))
            ->when($categoryId !== null, function (Builder $query) use ($categoryId): Builder {
                return $query->whereHas('groups', fn (Builder $query): Builder => $this->applyCategoryToGroup($query->openForEnrollment(), $categoryId));
            })
            ->ordered()
            ->limit(6)
            ->get();
        $pricingPackages = PricingPackage::query()
            ->forPublicList()
            ->with([
                'course' => fn ($query) => $query->forAcademyList(),
                'category:id,name_translations,code,slug',
            ])
            ->active()
            ->visibleOnSite()
            ->featured()
            ->when($categoryId !== null, fn (Builder $query): Builder => $this->applyCategoryToPricing($query, $categoryId))
            ->when($this->locationFilters->hasActive($filters), function (Builder $query) use ($filters): Builder {
                return $query->whereHas('course.groups', function (Builder $query) use ($filters): void {
                    $query
                        ->openForEnrollment()
                        ->whereHas('branch', fn (Builder $query): Builder => $this->locationFilters->applyPublicLocation($query, $filters));
                });
            })
            ->ordered()
            ->limit(4)
            ->get();
        $page = LandingPage::query()
            ->publicHome()
            ->firstOrFail();
        $sitePage = SitePage::query()
            ->active()
            ->published()
            ->where('type', 'home')
            ->ordered()
            ->first();

        return [
            'page' => $page,
            'sitePage' => $sitePage,
            'offers' => $page->translatedOfferCards(),
            'programs' => $programs,
            'upcomingGroups' => $upcomingGroups,
            'branches' => $branches,
            'pricingPackages' => $pricingPackages,
            'filters' => $filters,
            'filterOptions' => [
                'countries' => $locationOptions['countries'],
                'cities' => $locationOptions['cities'],
                'categories' => CourseCategory::query()
                    ->select(['id', 'code', 'slug', 'name_translations'])
                    ->active()
                    ->visibleOnSite()
                    ->ordered()
                    ->get(),
            ],
            'selectedCategory' => $selectedCategory,
            'selectedBranch' => $this->locationFilters->selectedBranch($filterBranches, $filters),
            'hasActiveFilters' => collect($filters)->contains(fn (?string $value): bool => filled($value)),
            'courseContextQuery' => collect([
                'country' => $filters['country'],
                'city' => $filters['city'],
            ])->filter(fn (?string $value): bool => filled($value))->all(),
            'testimonials' => Testimonial::query()
                ->forPublicList()
                ->with([
                    'course:id,title,title_translations,name_translations,slug',
                    'branch:id,name,name_translations,country,country_translations,city,city_translations',
                ])
                ->published()
                ->featured()
                ->ordered()
                ->limit(3)
                ->get(),
            'settings' => SiteSetting::query()
                ->public()
                ->get(['key', 'value'])
                ->mapWithKeys(fn (SiteSetting $setting): array => [$setting->key => $setting->value])
                ->all(),
            'stats' => [
                'students' => StudentProfile::query()
                    ->whereIn('status', [StudentStatus::Lead->value, StudentStatus::Enrolled->value, StudentStatus::Graduated->value])
                    ->count(),
                'pass_rate' => 92,
                'instructors' => Instructor::query()
                    ->where('status', 'active')
                    ->count(),
                'vehicles' => Vehicle::query()
                    ->where('status', 'active')
                    ->count(),
            ],
            'steps' => [
                ['title' => tkey('website.home.steps.application.title'), 'body' => tkey('website.home.steps.application.body')],
                ['title' => tkey('website.home.steps.theory.title'), 'body' => tkey('website.home.steps.theory.body')],
                ['title' => tkey('website.home.steps.practice.title'), 'body' => tkey('website.home.steps.practice.body')],
                ['title' => tkey('website.home.steps.exams.title'), 'body' => tkey('website.home.steps.exams.body')],
            ],
            'faq' => [
                ['question' => tkey('website.home.faq.online.question'), 'answer' => tkey('website.home.faq.online.answer')],
                ['question' => tkey('website.home.faq.instructor.question'), 'answer' => tkey('website.home.faq.instructor.answer')],
                ['question' => tkey('website.home.faq.documents.question'), 'answer' => tkey('website.home.faq.documents.answer')],
                ['question' => tkey('website.home.faq.intensive.question'), 'answer' => tkey('website.home.faq.intensive.answer')],
            ],
            'seoTitle' => $sitePage?->displaySeoTitle() ?: $page->displayTitle(),
            'seoDescription' => $sitePage?->displaySeoDescription() ?: $page->displayText('hero_summary'),
            'ogTitle' => $sitePage?->displayOgTitle(),
            'ogDescription' => $sitePage?->displayOgDescription(),
            'canonical' => $sitePage?->canonical_url ?: route('website.home'),
            'ogImage' => $sitePage?->og_image,
            'isIndexable' => $sitePage?->is_indexable ?? true,
        ];
    }

    /**
     * @return array{country: string, city: string, category: string}
     */
    private function filtersFromRequest(Request $request, Collection $filterBranches): array
    {
        return [
            ...$this->locationFilters->normalize($this->locationFilters->fromRequest($request), $filterBranches),
            'category' => trim((string) $request->query('category', '')),
        ];
    }

    /**
     * @param  array{country: string, city: string, category: string}  $filters
     */
    private function selectedCategory(array $filters): ?CourseCategory
    {
        if (blank($filters['category'])) {
            return null;
        }

        return CourseCategory::query()
            ->select(['id', 'code', 'slug', 'name_translations'])
            ->active()
            ->visibleOnSite()
            ->where('slug', $filters['category'])
            ->first();
    }

    private function applyCategoryToGroup(Builder $query, int $categoryId): Builder
    {
        return $query->where(function (Builder $query) use ($categoryId): void {
            $query
                ->where('course_category_id', $categoryId)
                ->orWhereHas('trainingProgram', fn (Builder $query): Builder => $query->where('course_category_id', $categoryId));
        });
    }

    private function applyCategoryToPricing(Builder $query, int $categoryId): Builder
    {
        return $query->where(function (Builder $query) use ($categoryId): void {
            $query
                ->where('course_category_id', $categoryId)
                ->orWhereHas('course', fn (Builder $query): Builder => $query->where('course_category_id', $categoryId));
        });
    }
}
