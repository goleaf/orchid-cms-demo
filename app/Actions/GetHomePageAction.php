<?php

namespace App\Actions;

use App\Enums\StudentStatus;
use App\Models\Branch;
use App\Models\Instructor;
use App\Models\KnowledgeArticle;
use App\Models\LandingPage;
use App\Models\StudentProfile;
use App\Models\StudentReview;
use App\Models\TrainingGroup;
use App\Models\TrainingProgram;
use App\Models\Vehicle;

class GetHomePageAction
{
    /**
     * @return array<string, mixed>
     */
    public function handle(): array
    {
        $page = LandingPage::query()
            ->publicHome()
            ->firstOrFail();

        return [
            'page' => $page,
            'offers' => $page->offerCards(),
            'programs' => TrainingProgram::query()
                ->forAcademyList()
                ->active()
                ->withCount('groups')
                ->orderBy('license_category')
                ->orderBy('title')
                ->limit(12)
                ->get(),
            'upcomingGroups' => TrainingGroup::query()
                ->operationalList()
                ->with([
                    'branch:id,name,city',
                    'trainingProgram:id,title,slug,license_category,price_cents',
                    'instructor:id,name',
                ])
                ->withCount('enrollments')
                ->whereIn('status', ['planned', 'recruiting'])
                ->orderBy('starts_on')
                ->limit(6)
                ->get(),
            'branches' => Branch::query()
                ->forAdminList()
                ->withCount(['instructors', 'vehicles', 'groups'])
                ->where('is_active', true)
                ->orderBy('city')
                ->limit(6)
                ->get(),
            'featuredInstructors' => Instructor::query()
                ->forPublicDirectory()
                ->with(['branch:id,name,city', 'vehicles:id,instructor_id,make,model,transmission'])
                ->where('status', 'active')
                ->orderByDesc('rating')
                ->limit(4)
                ->get(),
            'featuredVehicles' => Vehicle::query()
                ->forFleetList()
                ->with(['branch:id,name,city', 'instructor:id,name'])
                ->orderBy('make')
                ->limit(4)
                ->get(),
            'reviews' => StudentReview::query()
                ->forPublicList()
                ->published()
                ->with(['trainingProgram:id,title,slug', 'instructor:id,name'])
                ->orderByDesc('published_at')
                ->limit(3)
                ->get(),
            'articles' => KnowledgeArticle::query()
                ->forPublicList()
                ->published()
                ->orderByDesc('published_at')
                ->limit(3)
                ->get(),
            'stats' => [
                'students' => StudentProfile::query()
                    ->whereIn('status', [StudentStatus::Lead->value, StudentStatus::Enrolled->value, StudentStatus::Graduated->value])
                    ->count(),
                'pass_rate' => 92,
                'instructors' => Instructor::query()
                    ->where('status', 'active')
                    ->count(),
                'vehicles' => Vehicle::query()
                    ->where('status', 'active')
                    ->count(),
                'rating' => round((float) StudentReview::query()->published()->avg('rating'), 1),
            ],
            'steps' => [
                ['title' => 'Application and documents', 'body' => 'Choose a category, branch, group, language, and preferred schedule. The CRM creates a lead for the manager.'],
                ['title' => 'Theory and LMS', 'body' => 'Students follow the program modules, theory plan, and preparation materials before practice starts.'],
                ['title' => 'Driving practice', 'body' => 'The schedule links students with instructors, cars, branches, and training topics.'],
                ['title' => 'Internal and state exams', 'body' => 'Exam attempts, documents, payments, and readiness are tracked in one operational system.'],
            ],
            'faq' => [
                ['question' => 'Can I study online?', 'answer' => 'Yes. Theory can be online, offline, or mixed depending on the program and group.'],
                ['question' => 'Can I choose an instructor?', 'answer' => 'Yes. The application form supports instructor preference, and managers confirm availability.'],
                ['question' => 'Which documents are needed?', 'answer' => 'ID, medical certificate, photo, and signed training agreement are tracked in the document module.'],
                ['question' => 'Do you support intensive courses?', 'answer' => 'Yes. Intensive and exam-retake programs are available as separate categories.'],
            ],
            'seoTitle' => $page->title,
            'seoDescription' => $page->hero_summary,
        ];
    }
}
