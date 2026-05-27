<?php

namespace App\Actions;

use App\Models\Branch;
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
            ->where('is_active', true)
            ->with([
                'groups' => fn ($query) => $query
                    ->operationalList()
                    ->visibleOnSite()
                    ->with([
                        'trainingProgram:id,title,title_translations,slug,license_category,price_cents',
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
            'seoTitle' => $publicBranch->displaySeoTitle().' | '.tkey('website.brand.name'),
            'seoDescription' => $publicBranch->displaySeoDescription() ?: tkey('website.branches.seo.description', [
                'branch' => $publicBranch->displayName(),
                'city' => $publicBranch->displayCity(),
            ]),
            'canonical' => $publicBranch->canonical_url,
            'ogImage' => $publicBranch->open_graph_image,
        ];
    }
}
