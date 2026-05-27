<?php

namespace App\Actions;

use App\Models\Branch;
use App\Models\SiteSetting;
use App\Models\TrainingProgram;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class GetBranchPageAction
{
    /**
     * @return array<string, mixed>
     */
    public function handle(Branch $branch): array
    {
        $publicBranch = Branch::query()
            ->forAdminList()
            ->whereKey($branch->id)
            ->active()
            ->visibleOnSite()
            ->with([
                'groups' => fn ($query) => $query
                    ->operationalList()
                    ->openForEnrollment()
                    ->with([
                        'trainingProgram:id,title,title_translations,name_translations,slug,license_category,price_cents,is_active,is_visible_on_site',
                        'instructor:id,name',
                    ])
                    ->withCount('enrollments')
                    ->orderBy('starts_on')
                    ->limit(20),
                'instructors' => fn ($query) => $query
                    ->forPublicDirectory()
                    ->withCount('reviews')
                    ->where('status', 'active')
                    ->orderByDesc('rating')
                    ->limit(8),
                'vehicles' => fn ($query) => $query
                    ->forFleetList()
                    ->with('instructor:id,name')
                    ->orderBy('make')
                    ->limit(8),
            ])
            ->withCount(['groups', 'instructors', 'vehicles'])
            ->first();

        if ($publicBranch === null) {
            throw new NotFoundHttpException;
        }

        return [
            'branch' => $publicBranch,
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
            'seoTitle' => $publicBranch->displaySeoTitle().' | '.tkey('website.brand.name'),
            'seoDescription' => $publicBranch->displaySeoDescription() ?: tkey('website.branches.seo.description', [
                'branch' => $publicBranch->displayName(),
                'city' => $publicBranch->displayCity(),
            ]),
            'ogTitle' => $publicBranch->displayOgTitle().' | '.tkey('website.brand.name'),
            'ogDescription' => $publicBranch->displayOgDescription(),
            'canonical' => $publicBranch->canonical_url ?: route('website.branches.show', ['branch' => $publicBranch->slug]),
            'ogImage' => $publicBranch->open_graph_image,
            'isIndexable' => $publicBranch->is_indexable,
        ];
    }
}
