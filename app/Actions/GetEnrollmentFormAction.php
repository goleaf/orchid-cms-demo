<?php

namespace App\Actions;

use App\Models\Branch;
use App\Models\Instructor;
use App\Models\TrainingGroup;
use App\Models\TrainingProgram;

class GetEnrollmentFormAction
{
    /**
     * @return array<string, mixed>
     */
    public function handle(array $tracking = []): array
    {
        return [
            'programs' => TrainingProgram::query()
                ->forAcademyList()
                ->active()
                ->orderBy('license_category')
                ->orderBy('title')
                ->get(),
            'branches' => Branch::query()
                ->forAdminList()
                ->where('is_active', true)
                ->orderBy('city')
                ->get(),
            'groups' => TrainingGroup::query()
                ->operationalList()
                ->with([
                    'branch:id,name,city',
                    'trainingProgram:id,title,license_category',
                ])
                ->whereIn('status', ['planned', 'recruiting'])
                ->orderBy('starts_on')
                ->limit(20)
                ->get(),
            'instructors' => Instructor::query()
                ->forPublicDirectory()
                ->with('branch:id,name,city')
                ->where('status', 'active')
                ->orderBy('name')
                ->get(),
            'formats' => ['offline' => 'Offline', 'online' => 'Online', 'mixed' => 'Mixed'],
            'languages' => ['Lithuanian', 'English', 'Russian', 'Polish'],
            'tracking' => [
                'source' => $tracking['source'] ?? 'website',
                'utm_source' => $tracking['utm_source'] ?? null,
                'utm_medium' => $tracking['utm_medium'] ?? null,
                'utm_campaign' => $tracking['utm_campaign'] ?? null,
                'utm_term' => $tracking['utm_term'] ?? null,
                'utm_content' => $tracking['utm_content'] ?? null,
                'referrer_url' => $tracking['referrer_url'] ?? null,
                'program' => $tracking['program'] ?? null,
                'instructor' => $tracking['instructor'] ?? null,
            ],
            'seoTitle' => 'Online enrollment | DrivePro Academy',
            'seoDescription' => 'Choose a category, branch, group, instructor, language, and preferred time to apply for driving lessons online.',
        ];
    }
}
