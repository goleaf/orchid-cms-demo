<?php

namespace App\Actions;

use App\Models\Branch;
use App\Models\SitePage;
use App\Models\SiteSetting;
use App\Models\TrainingProgram;

class GetContactPageAction
{
    /**
     * @return array<string, mixed>
     */
    public function handle(): array
    {
        $sitePage = SitePage::query()
            ->active()
            ->published()
            ->where('type', 'contacts')
            ->ordered()
            ->first();

        return [
            'branches' => Branch::query()
                ->forAdminList()
                ->withCount(['instructors', 'vehicles', 'groups'])
                ->active()
                ->visibleOnSite()
                ->ordered()
                ->get(),
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
            'seoTitle' => $sitePage?->displaySeoTitle() ?: tkey('website.contacts.seo.title'),
            'seoDescription' => $sitePage?->displaySeoDescription() ?: tkey('website.contacts.seo.description'),
            'ogTitle' => $sitePage?->displayOgTitle(),
            'ogDescription' => $sitePage?->displayOgDescription(),
            'ogImage' => $sitePage?->og_image,
            'canonical' => $sitePage?->canonical_url ?: route('website.contacts'),
            'isIndexable' => $sitePage?->is_indexable ?? true,
        ];
    }
}
