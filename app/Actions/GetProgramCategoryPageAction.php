<?php

namespace App\Actions;

use App\Models\Branch;
use App\Models\Instructor;
use App\Models\TrainingProgram;
use App\Models\Vehicle;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class GetProgramCategoryPageAction
{
    /**
     * @return array<string, mixed>
     */
    public function handle(TrainingProgram $program): array
    {
        $publicProgram = TrainingProgram::query()
            ->forAcademyList()
            ->active()
            ->with([
                'groups' => fn ($query) => $query
                    ->select([
                        'id',
                        'branch_id',
                        'training_program_id',
                        'instructor_id',
                        'name',
                        'code',
                        'status',
                        'capacity',
                        'starts_on',
                        'meeting_days',
                        'meeting_time',
                    ])
                    ->with(['branch:id,name,name_translations,city,city_translations', 'instructor:id,name'])
                    ->withCount('enrollments')
                    ->visibleOnSite()
                    ->orderBy('starts_on')
                    ->limit(8),
            ])
            ->whereKey($program->id)
            ->first();

        if ($publicProgram === null) {
            throw new NotFoundHttpException;
        }

        return [
            'program' => $publicProgram,
            'branches' => Branch::query()
                ->forAdminList()
                ->withCount('groups')
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('city')
                ->get(),
            'instructors' => Instructor::query()
                ->forPublicDirectory()
                ->with('branch:id,name,name_translations,city,city_translations')
                ->where('status', 'active')
                ->whereJsonContains('categories', $publicProgram->license_category)
                ->orderByDesc('rating')
                ->limit(8)
                ->get(),
            'vehicles' => Vehicle::query()
                ->forFleetList()
                ->with(['branch:id,name,name_translations,city,city_translations', 'instructor:id,name'])
                ->where('license_category', $publicProgram->license_category)
                ->orderBy('make')
                ->limit(8)
                ->get(),
            'seoTitle' => $publicProgram->displaySeoTitle().' | '.tkey('website.brand.name'),
            'seoDescription' => $publicProgram->displaySeoDescription(),
            'canonical' => $publicProgram->canonical_url,
            'ogImage' => $publicProgram->open_graph_image,
        ];
    }
}
