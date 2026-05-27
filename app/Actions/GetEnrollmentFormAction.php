<?php

namespace App\Actions;

use App\Models\Branch;
use App\Models\Instructor;
use App\Models\TrainingGroup;
use App\Models\TrainingProgram;
use App\Services\LocaleManager;

class GetEnrollmentFormAction
{
    /**
     * @return array<string, mixed>
     */
    public function handle(array $tracking = []): array
    {
        $languageOptions = app(LocaleManager::class)->languageOptions();

        return [
            'programs' => TrainingProgram::query()
                ->forAcademyList()
                ->active()
                ->orderBy('sort_order')
                ->orderBy('license_category')
                ->orderBy('title')
                ->get(),
            'branches' => Branch::query()
                ->forAdminList()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('city')
                ->get(),
            'groups' => TrainingGroup::query()
                ->operationalList()
                ->with([
                    'branch:id,name,name_translations,city,city_translations',
                    'trainingProgram:id,title,title_translations,license_category',
                ])
                ->visibleOnSite()
                ->orderBy('starts_on')
                ->limit(20)
                ->get(),
            'instructors' => Instructor::query()
                ->forPublicDirectory()
                ->with('branch:id,name,name_translations,city,city_translations')
                ->where('status', 'active')
                ->orderBy('name')
                ->get(),
            'formats' => [
                'offline' => tkey('website.courses.formats.offline'),
                'online' => tkey('website.courses.formats.online'),
                'mixed' => tkey('website.courses.formats.hybrid'),
            ],
            'languages' => $languageOptions,
            'tracking' => [
                'source' => $tracking['source'] ?? 'website',
                'utm_source' => $tracking['utm_source'] ?? null,
                'utm_medium' => $tracking['utm_medium'] ?? null,
                'utm_campaign' => $tracking['utm_campaign'] ?? null,
                'utm_term' => $tracking['utm_term'] ?? null,
                'utm_content' => $tracking['utm_content'] ?? null,
                'referrer_url' => $tracking['referrer_url'] ?? null,
                'landing_page' => $tracking['landing_page'] ?? null,
                'form_page' => $tracking['form_page'] ?? null,
                'form_name' => $tracking['form_name'] ?? 'enrollment',
                'program' => $tracking['program'] ?? null,
                'branch' => $tracking['branch'] ?? null,
                'group' => $tracking['group'] ?? null,
                'instructor' => $tracking['instructor'] ?? null,
            ],
            'seoTitle' => tkey('website.apply.seo.title'),
            'seoDescription' => tkey('website.apply.seo.description'),
        ];
    }
}
