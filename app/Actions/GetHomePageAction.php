<?php

namespace App\Actions;

use App\Enums\StudentStatus;
use App\Models\Branch;
use App\Models\Instructor;
use App\Models\LandingPage;
use App\Models\PricingPackage;
use App\Models\SitePage;
use App\Models\SiteSetting;
use App\Models\StudentProfile;
use App\Models\Testimonial;
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
        $sitePage = SitePage::query()
            ->active()
            ->published()
            ->where('type', 'home')
            ->ordered()
            ->first();

        return [
            'page' => $page,
            'sitePage' => $sitePage,
            'offers' => $page->translatedOfferCards(),
            'programs' => TrainingProgram::query()
                ->forAcademyList()
                ->addSelect(['name_translations', 'is_visible_on_site', 'is_featured'])
                ->active()
                ->visibleOnSite()
                ->withCount('groups')
                ->orderBy('sort_order')
                ->orderBy('license_category')
                ->orderBy('title')
                ->limit(12)
                ->get(),
            'upcomingGroups' => TrainingGroup::query()
                ->operationalList()
                ->with([
                    'branch:id,name,name_translations,city,city_translations',
                    'trainingProgram:id,title,title_translations,slug,license_category,price_cents',
                    'instructor:id,name',
                ])
                ->withCount('enrollments')
                ->openForEnrollment()
                ->orderBy('starts_on')
                ->limit(6)
                ->get(),
            'branches' => Branch::query()
                ->forAdminList()
                ->withCount(['instructors', 'vehicles', 'groups'])
                ->active()
                ->visibleOnSite()
                ->ordered()
                ->limit(6)
                ->get(),
            'pricingPackages' => PricingPackage::query()
                ->forPublicList()
                ->with([
                    'course' => fn ($query) => $query->forAcademyList(),
                    'category:id,name_translations,code,slug',
                ])
                ->active()
                ->visibleOnSite()
                ->featured()
                ->ordered()
                ->limit(4)
                ->get(),
            'testimonials' => Testimonial::query()
                ->forPublicList()
                ->with([
                    'course:id,title,title_translations,name_translations,slug',
                    'branch:id,name,name_translations,city,city_translations',
                ])
                ->published()
                ->featured()
                ->ordered()
                ->limit(3)
                ->get(),
            'settings' => SiteSetting::query()
                ->public()
                ->get(['key', 'value'])
                ->mapWithKeys(fn (SiteSetting $setting): array => [$setting->key => $setting->value])
                ->all(),
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
            ],
            'steps' => [
                ['title' => tkey('website.home.steps.application.title'), 'body' => tkey('website.home.steps.application.body')],
                ['title' => tkey('website.home.steps.theory.title'), 'body' => tkey('website.home.steps.theory.body')],
                ['title' => tkey('website.home.steps.practice.title'), 'body' => tkey('website.home.steps.practice.body')],
                ['title' => tkey('website.home.steps.exams.title'), 'body' => tkey('website.home.steps.exams.body')],
            ],
            'faq' => [
                ['question' => tkey('website.home.faq.online.question'), 'answer' => tkey('website.home.faq.online.answer')],
                ['question' => tkey('website.home.faq.instructor.question'), 'answer' => tkey('website.home.faq.instructor.answer')],
                ['question' => tkey('website.home.faq.documents.question'), 'answer' => tkey('website.home.faq.documents.answer')],
                ['question' => tkey('website.home.faq.intensive.question'), 'answer' => tkey('website.home.faq.intensive.answer')],
            ],
            'seoTitle' => $sitePage?->displaySeoTitle() ?: $page->displayTitle(),
            'seoDescription' => $sitePage?->displaySeoDescription() ?: $page->displayText('hero_summary'),
            'canonical' => route('website.home'),
            'ogImage' => $sitePage?->og_image,
            'isIndexable' => $sitePage?->is_indexable ?? true,
        ];
    }
}
