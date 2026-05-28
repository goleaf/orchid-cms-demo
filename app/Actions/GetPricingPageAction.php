<?php

namespace App\Actions;

use App\Models\Branch;
use App\Models\CourseCategory;
use App\Models\PricingPackage;
use App\Models\SitePage;
use App\Models\SiteSetting;
use App\Models\TrainingGroup;
use App\Models\TrainingProgram;
use App\Support\Website\BranchLocationFilters;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class GetPricingPageAction
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
        $hasLocationFilter = $this->locationFilters->hasActive($filters);
        $locationOptions = $this->locationFilters->options($filterBranches, $filters);
        $allPrograms = TrainingProgram::query()
            ->forAcademyList()
            ->addSelect(['course_category_id', 'name_translations', 'duration_translations', 'is_visible_on_site'])
            ->active()
            ->visibleOnSite()
            ->orderBy('sort_order')
            ->orderBy('license_category')
            ->orderBy('title')
            ->get();
        $availablePrograms = $hasLocationFilter
            ? TrainingProgram::query()
                ->forAcademyList()
                ->addSelect(['course_category_id', 'name_translations', 'duration_translations', 'is_visible_on_site'])
                ->active()
                ->visibleOnSite()
                ->whereHas('groups', function (Builder $query) use ($filters): void {
                    $query
                        ->openForEnrollment()
                        ->whereHas('branch', fn (Builder $query): Builder => $this->locationFilters->applyPublicLocation($query, $filters));
                })
                ->orderBy('sort_order')
                ->orderBy('license_category')
                ->orderBy('title')
                ->get()
            : $allPrograms;
        $programs = TrainingProgram::query()
            ->forAcademyList()
            ->addSelect(['course_category_id', 'name_translations', 'duration_translations', 'is_visible_on_site'])
            ->active()
            ->visibleOnSite()
            ->withCount('groups')
            ->when($categoryId !== null, fn (Builder $query): Builder => $query->where('course_category_id', $categoryId))
            ->when(filled($filters['course']), fn (Builder $query): Builder => $query->where('slug', $filters['course']))
            ->when(filled($filters['format']), fn (Builder $query): Builder => $query->where('format', $filters['format']))
            ->when(filled($filters['duration']), fn (Builder $query): Builder => $query->where('duration_weeks', (int) $filters['duration']))
            ->when(filled($filters['theory_min']), fn (Builder $query): Builder => $query->where('theory_hours', '>=', (int) $filters['theory_min']))
            ->when(filled($filters['practice_min']), fn (Builder $query): Builder => $query->where('practice_hours', '>=', (int) $filters['practice_min']))
            ->when(filled($filters['price_min']), fn (Builder $query): Builder => $query->where('price_cents', '>=', (int) round(((float) $filters['price_min']) * 100)))
            ->when(filled($filters['price_max']), fn (Builder $query): Builder => $query->where('price_cents', '<=', (int) round(((float) $filters['price_max']) * 100)))
            ->when($hasLocationFilter, function (Builder $query) use ($filters): Builder {
                return $query->whereHas('groups', function (Builder $query) use ($filters): void {
                    $query
                        ->openForEnrollment()
                        ->whereHas('branch', fn (Builder $query): Builder => $this->locationFilters->applyPublicLocation($query, $filters));
                });
            })
            ->orderBy('sort_order')
            ->orderBy('license_category')
            ->orderBy('title')
            ->get();
        $hasActiveFilters = collect($filters)->contains(fn (?string $value): bool => filled($value));
        $programIds = $programs->pluck('id')->all();
        $packages = PricingPackage::query()
            ->forPublicList()
            ->with([
                'course' => fn ($query) => $query->forAcademyList(),
                'category:id,name_translations,code,slug',
            ])
            ->active()
            ->visibleOnSite()
            ->when($categoryId !== null, fn (Builder $query): Builder => $this->applyCategoryToPricing($query, $categoryId))
            ->when(filled($filters['course']), function (Builder $query) use ($filters): Builder {
                return $query->whereHas('course', fn (Builder $query): Builder => $query->where('slug', $filters['course']));
            })
            ->when(filled($filters['format']), function (Builder $query) use ($filters): Builder {
                return $query->whereHas('course', fn (Builder $query): Builder => $query->where('format', $filters['format']));
            })
            ->when(filled($filters['duration']), function (Builder $query) use ($filters): Builder {
                return $query->whereHas('course', fn (Builder $query): Builder => $query->where('duration_weeks', (int) $filters['duration']));
            })
            ->when(filled($filters['theory_min']), fn (Builder $query): Builder => $query->where('theory_hours', '>=', (int) $filters['theory_min']))
            ->when(filled($filters['practice_min']), fn (Builder $query): Builder => $query->where('practice_hours', '>=', (int) $filters['practice_min']))
            ->when(filled($filters['price_min']), fn (Builder $query): Builder => $query->where('price', '>=', (float) $filters['price_min']))
            ->when(filled($filters['price_max']), fn (Builder $query): Builder => $query->where('price', '<=', (float) $filters['price_max']))
            ->when($hasLocationFilter, function (Builder $query) use ($filters): Builder {
                return $query->whereHas('course.groups', function (Builder $query) use ($filters): void {
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
                'branch:id,name,name_translations,country,country_translations,city,city_translations',
                'trainingProgram:id,title,title_translations,name_translations,slug,license_category,price_cents',
            ])
            ->withCount('enrollments')
            ->openForEnrollment()
            ->when($hasActiveFilters, fn (Builder $query): Builder => $query->whereIn('training_program_id', $programIds))
            ->when($hasLocationFilter, fn (Builder $query): Builder => $query->whereHas('branch', fn (Builder $query): Builder => $this->locationFilters->applyPublicLocation($query, $filters)))
            ->ordered()
            ->limit(20)
            ->get();
        $sitePage = SitePage::query()
            ->active()
            ->published()
            ->where('type', 'pricing')
            ->ordered()
            ->first();

        return [
            'programs' => $programs,
            'packages' => $packages,
            'filters' => $filters,
            'filterOptions' => [
                'courses' => $availablePrograms,
                'categories' => CourseCategory::query()
                    ->select(['id', 'code', 'slug', 'name_translations'])
                    ->active()
                    ->visibleOnSite()
                    ->ordered()
                    ->get(),
                'formats' => $this->formatOptions($availablePrograms),
                'durations' => $this->durationOptions($availablePrograms),
                'countries' => $locationOptions['countries'],
                'cities' => $locationOptions['cities'],
            ],
            'hasActiveFilters' => $hasActiveFilters,
            'selectedProgram' => filled($filters['course'])
                ? $allPrograms->firstWhere('slug', $filters['course'])
                : null,
            'courseContextQuery' => collect([
                'country' => $filters['country'],
                'city' => $filters['city'],
            ])->filter(fn (?string $value): bool => filled($value))->all(),
            'branches' => $this->locationFilters->filterBranches($filterBranches, $filters),
            'groups' => $groups,
            'settings' => SiteSetting::query()
                ->public()
                ->get(['key', 'value'])
                ->mapWithKeys(fn (SiteSetting $setting): array => [$setting->key => $setting->value])
                ->all(),
            'seoTitle' => $sitePage?->displaySeoTitle() ?: tkey('website.prices.seo.title'),
            'seoDescription' => $sitePage?->displaySeoDescription() ?: tkey('website.prices.seo.description'),
            'ogTitle' => $sitePage?->displayOgTitle(),
            'ogDescription' => $sitePage?->displayOgDescription(),
            'ogImage' => $sitePage?->og_image,
            'canonical' => $sitePage?->canonical_url ?: route('website.pricing'),
            'isIndexable' => $sitePage?->is_indexable ?? true,
        ];
    }

    /**
     * @param  Collection<int, Branch>  $filterBranches
     * @return array{course: string, category: string, format: string, duration: string, theory_min: string, practice_min: string, price_min: string, price_max: string, country: string, city: string}
     */
    private function filtersFromRequest(Request $request, Collection $filterBranches): array
    {
        return [
            'course' => trim((string) $request->query('course', '')),
            'category' => trim((string) $request->query('category', '')),
            'format' => trim((string) $request->query('format', '')),
            'duration' => $this->integerFilter($request->query('duration')),
            'theory_min' => $this->integerFilter($request->query('theory_min')),
            'practice_min' => $this->integerFilter($request->query('practice_min')),
            'price_min' => $this->decimalFilter($request->query('price_min')),
            'price_max' => $this->decimalFilter($request->query('price_max')),
            ...$this->locationFilters->normalize($this->locationFilters->fromRequest($request), $filterBranches),
        ];
    }

    /**
     * @param  array{course: string, category: string, format: string, duration: string, theory_min: string, practice_min: string, price_min: string, price_max: string, country: string, city: string}  $filters
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

    private function applyCategoryToPricing(Builder $query, int $categoryId): Builder
    {
        return $query->where(function (Builder $query) use ($categoryId): void {
            $query
                ->where('course_category_id', $categoryId)
                ->orWhereHas('course', fn (Builder $query): Builder => $query->where('course_category_id', $categoryId));
        });
    }

    /**
     * @param  Collection<int, TrainingProgram>  $programs
     * @return Collection<int, array{value: string, label: string}>
     */
    private function formatOptions(Collection $programs): Collection
    {
        return $programs
            ->pluck('format')
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->map(fn (string $format): array => [
                'value' => $format,
                'label' => tkey('website.courses.formats.'.$format),
            ]);
    }

    /**
     * @param  Collection<int, TrainingProgram>  $programs
     * @return Collection<int, int>
     */
    private function durationOptions(Collection $programs): Collection
    {
        return $programs
            ->pluck('duration_weeks')
            ->filter()
            ->map(fn (mixed $duration): int => (int) $duration)
            ->unique()
            ->sort()
            ->values();
    }

    private function integerFilter(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        $value = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);

        return $value === false ? '' : (string) $value;
    }

    private function decimalFilter(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        $value = filter_var($value, FILTER_VALIDATE_FLOAT);

        return $value === false || $value < 0 ? '' : (string) $value;
    }
}
