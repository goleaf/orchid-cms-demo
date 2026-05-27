<?php

namespace App\Actions;

use App\Models\Instructor;

class GetInstructorDirectoryAction
{
    /**
     * @return array<string, mixed>
     */
    public function handle(): array
    {
        return [
            'instructors' => Instructor::query()
                ->forPublicDirectory()
                ->with([
                    'branch:id,name,name_translations,city,city_translations',
                    'vehicles:id,instructor_id,make,model,registration_number,transmission,license_category,status',
                ])
                ->withCount('reviews')
                ->where('status', 'active')
                ->orderByDesc('rating')
                ->orderBy('name')
                ->simplePaginate(12)
                ->withQueryString(),
            'seoTitle' => tkey('website.instructors.seo.title'),
            'seoDescription' => tkey('website.instructors.seo.description'),
        ];
    }
}
