<?php

namespace App\Actions;

use App\Models\StudentReview;

class GetPublicReviewsAction
{
    /**
     * @return array<string, mixed>
     */
    public function handle(): array
    {
        return [
            'reviews' => StudentReview::query()
                ->forPublicList()
                ->published()
                ->with([
                    'trainingProgram:id,title,title_translations,slug',
                    'trainingGroup:id,name,name_translations,code',
                    'instructor:id,name,rating',
                ])
                ->orderByDesc('published_at')
                ->simplePaginate(12)
                ->withQueryString(),
            'seoTitle' => tkey('website.reviews.seo.title'),
            'seoDescription' => tkey('website.reviews.seo.description'),
        ];
    }
}
