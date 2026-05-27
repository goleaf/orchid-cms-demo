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
                    'trainingProgram:id,title,slug',
                    'trainingGroup:id,name,code',
                    'instructor:id,name,rating',
                ])
                ->orderByDesc('published_at')
                ->simplePaginate(12)
                ->withQueryString(),
            'seoTitle' => 'Student reviews | DrivePro Academy',
            'seoDescription' => 'Moderated student reviews, school rating, instructor ratings, course links, video reviews, and admin replies.',
        ];
    }
}
