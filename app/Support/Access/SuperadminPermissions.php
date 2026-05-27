<?php

namespace App\Support\Access;

class SuperadminPermissions
{
    /**
     * @return array<int, string>
     */
    public static function all(): array
    {
        return [
            'platform.index',
            'platform.main',
            'platform.content.home',
            'platform.operations.branches',
            'platform.operations.instructors',
            'platform.operations.groups',
            'platform.crm.students',
            'platform.lms.programs',
            'platform.schedule.lessons',
            'platform.fleet.vehicles',
            'platform.exams',
            'platform.finance.payments',
            'platform.documents',
            'platform.marketing.campaigns',
            'platform.marketing.pipeline',
            'platform.marketing.leads',
            'platform.marketing.templates',
            'platform.crm.pipeline',
            'platform.crm.leads',
            'platform.crm.tasks',
            'platform.systems.roles',
            'platform.systems.users',
            'platform.systems.attachment',
            'website.view',
            'website.manage_pages',
            'website.manage_courses',
            'website.manage_course_categories',
            'website.manage_pricing',
            'website.manage_branches',
            'website.manage_groups',
            'website.manage_faq',
            'website.manage_testimonials',
            'website.manage_settings',
            'website.view_leads',
            'website.update_leads',
            'website.view_marketing',
            'website.preview',
            'system.languages.view',
            'system.languages.create',
            'system.languages.update',
            'system.languages.delete',
            'system.translations.view',
            'system.translations.update',
            'system.translations.export',
            'system.translations.import',
            'crm.leads.view',
            'crm.leads.create',
            'crm.leads.update',
            'crm.leads.delete',
            'crm.leads.archive',
            'crm.leads.assign',
            'crm.leads.change_status',
            'crm.leads.override_status_transition',
            'crm.leads.manage_tasks',
            'crm.leads.manage_dictionaries',
            'crm.leads.manage_tags',
            'crm.leads.view_marketing',
            'crm.leads.convert',
            'crm.leads.export',
            'crm.pipeline.view',
        ];
    }

    /**
     * @return array<string, bool>
     */
    public static function enabled(): array
    {
        return collect(static::all())
            ->mapWithKeys(fn (string $permission): array => [$permission => true])
            ->all();
    }
}
