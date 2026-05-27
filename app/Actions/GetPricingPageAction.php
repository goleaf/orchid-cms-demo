<?php

namespace App\Actions;

use App\Models\PricingPackage;
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
            'seoTitle' => tkey('website.prices.seo.title'),
            'seoDescription' => tkey('website.prices.seo.description'),
        ];
    }
}
