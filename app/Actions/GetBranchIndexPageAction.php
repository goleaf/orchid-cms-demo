<?php

namespace App\Actions;

use App\Models\Branch;
use App\Models\SiteSetting;
use App\Models\TrainingProgram;
use App\Support\Website\BranchLocationFilters;
use Illuminate\Http\Request;

class GetBranchIndexPageAction
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
        $filters = $this->locationFilters->normalize($this->locationFilters->fromRequest($request), $filterBranches);
        $locationOptions = $this->locationFilters->options($filterBranches, $filters);

        return [
            'branches' => Branch::query()
                ->forAdminList()
                ->with([
                    'groups' => fn ($query) => $query
                        ->operationalList()
                        ->with([
                            'trainingProgram:id,title,title_translations,name_translations,slug,license_category,price_cents,is_active,is_visible_on_site',
                            'instructor:id,name',
                        ])
                        ->withCount('enrollments')
                        ->openForEnrollment()
                        ->ordered()
                        ->limit(8),
                ])
                ->withCount(['instructors', 'vehicles', 'groups'])
                ->active()
                ->visibleOnSite()
                ->when($this->locationFilters->hasActive($filters), fn ($query) => $this->locationFilters->applyLocation($query, $filters))
                ->ordered()
                ->get(),
            'filters' => $filters,
            'filterOptions' => $locationOptions,
            'hasActiveFilters' => $this->locationFilters->hasActive($filters),
            'programs' => TrainingProgram::query()
                ->forAcademyList()
                ->addSelect(['name_translations', 'is_visible_on_site'])
                ->active()
                ->visibleOnSite()
                ->ordered()
                ->get(),
            'settings' => SiteSetting::query()
                ->public()
                ->get(['key', 'value'])
                ->mapWithKeys(fn (SiteSetting $setting): array => [$setting->key => $setting->value])
                ->all(),
            'seoTitle' => tkey('website.branches.title').' | '.tkey('website.brand.name'),
            'seoDescription' => tkey('website.contacts.seo.description'),
            'canonical' => route('website.branches.index'),
        ];
    }
}
