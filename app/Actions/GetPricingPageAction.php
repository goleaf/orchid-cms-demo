<?php

namespace App\Actions;

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
            ->withCount('groups')
            ->orderBy('sort_order')
            ->orderBy('license_category')
            ->orderBy('title')
            ->get();

        return [
            'programs' => $programs,
            'seoTitle' => tkey('website.prices.seo.title'),
            'seoDescription' => tkey('website.prices.seo.description'),
        ];
    }
}
