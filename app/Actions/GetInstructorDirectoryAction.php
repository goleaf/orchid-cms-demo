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
                    'branch:id,name,city',
                    'vehicles:id,instructor_id,make,model,registration_number,transmission,license_category,status',
                ])
                ->withCount('reviews')
                ->where('status', 'active')
                ->orderByDesc('rating')
                ->orderBy('name')
                ->simplePaginate(12)
                ->withQueryString(),
            'seoTitle' => 'Driving instructors | DrivePro Academy',
            'seoDescription' => 'Instructor profiles with experience, ratings, categories, languages, vehicles, branches, and teaching availability.',
        ];
    }
}
