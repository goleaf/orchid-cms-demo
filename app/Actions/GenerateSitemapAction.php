<?php

namespace App\Actions;

use App\Models\Branch;
use App\Models\SitePage;
use App\Models\TrainingProgram;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class GenerateSitemapAction
{
    /**
     * @return Collection<int, array{url: string, updated_at: Carbon|null}>
     */
    public function handle(): Collection
    {
        return collect()
            ->merge($this->staticUrls())
            ->merge($this->sitePageUrls())
            ->merge($this->courseUrls())
            ->merge($this->branchUrls())
            ->unique('url')
            ->values();
    }

    /**
     * @return Collection<int, array{url: string, updated_at: Carbon|null}>
     */
    private function staticUrls(): Collection
    {
        return collect([
            ['url' => route('website.home'), 'updated_at' => now()],
            ['url' => route('website.courses.index'), 'updated_at' => now()],
            ['url' => route('website.pricing'), 'updated_at' => now()],
            ['url' => route('website.branches.index'), 'updated_at' => now()],
            ['url' => route('website.contacts'), 'updated_at' => now()],
        ]);
    }

    /**
     * @return Collection<int, array{url: string, updated_at: Carbon|null}>
     */
    private function sitePageUrls(): Collection
    {
        return SitePage::query()
            ->select(['id', 'type', 'slug', 'is_indexable', 'is_active', 'published_at', 'updated_at'])
            ->active()
            ->published()
            ->indexable()
            ->ordered()
            ->get()
            ->map(fn (SitePage $page): ?array => $this->sitePageUrl($page))
            ->filter()
            ->values();
    }

    /**
     * @return Collection<int, array{url: string, updated_at: Carbon|null}>
     */
    private function courseUrls(): Collection
    {
        return TrainingProgram::query()
            ->forAcademyList()
            ->addSelect(['is_visible_on_site', 'is_indexable'])
            ->active()
            ->visibleOnSite()
            ->indexable()
            ->ordered()
            ->get()
            ->map(fn (TrainingProgram $course): array => [
                'url' => route('website.courses.show', $course),
                'updated_at' => $course->updated_at,
            ]);
    }

    /**
     * @return Collection<int, array{url: string, updated_at: Carbon|null}>
     */
    private function branchUrls(): Collection
    {
        return Branch::query()
            ->forAdminList()
            ->active()
            ->visibleOnSite()
            ->indexable()
            ->ordered()
            ->get()
            ->map(fn (Branch $branch): array => [
                'url' => route('website.branches.show', ['branch' => $branch->slug]),
                'updated_at' => $branch->updated_at,
            ]);
    }

    /**
     * @return array{url: string, updated_at: Carbon|null}|null
     */
    private function sitePageUrl(SitePage $page): ?array
    {
        $url = match ($page->type) {
            'home' => route('website.home'),
            'pricing' => route('website.pricing'),
            'contacts' => route('website.contacts'),
            'thank_you' => route('website.thank_you'),
            'custom', 'privacy_policy', 'terms', null => route('website.pages.show', $page),
            default => route('website.pages.show', $page),
        };

        return [
            'url' => $url,
            'updated_at' => $page->updated_at,
        ];
    }
}
