<?php

namespace App\Actions;

use App\Models\Branch;
use App\Models\PricingPackage;
use App\Models\SiteSetting;
use App\Models\TrainingGroup;
use App\Models\TrainingProgram;

class GetPricingPageAction
{
    /**
     * @return array<string, mixed>
     */
    public function handle(): array
    {
        $programs = TrainingProgram::query()
            ->forAcademyList()
            ->addSelect(['name_translations', 'is_visible_on_site'])
            ->active()
            ->visibleOnSite()
            ->withCount('groups')
            ->orderBy('sort_order')
            ->orderBy('license_category')
            ->orderBy('title')
            ->get();

        return [
            'programs' => $programs,
            'packages' => PricingPackage::query()
                ->forPublicList()
                ->with([
                    'course' => fn ($query) => $query->forAcademyList(),
                    'category:id,name_translations,code,slug',
                ])
                ->active()
                ->visibleOnSite()
                ->ordered()
                ->get(),
            'branches' => Branch::query()
                ->forAdminList()
                ->active()
                ->visibleOnSite()
                ->ordered()
                ->get(),
            'groups' => TrainingGroup::query()
                ->operationalList()
                ->with([
                    'branch:id,name,name_translations,city,city_translations',
                    'trainingProgram:id,title,title_translations,name_translations,slug,license_category,price_cents',
                ])
                ->withCount('enrollments')
                ->openForEnrollment()
                ->ordered()
                ->limit(20)
                ->get(),
            'settings' => SiteSetting::query()
                ->public()
                ->get(['key', 'value'])
                ->mapWithKeys(fn (SiteSetting $setting): array => [$setting->key => $setting->value])
                ->all(),
            'seoTitle' => tkey('website.prices.seo.title'),
            'seoDescription' => tkey('website.prices.seo.description'),
            'canonical' => route('website.pricing'),
        ];
    }
}
