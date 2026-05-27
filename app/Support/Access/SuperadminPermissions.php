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
            'platform.systems.roles',
            'platform.systems.users',
            'platform.systems.attachment',
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
            'crm.leads.assign',
            'crm.leads.change_status',
            'crm.leads.manage_dictionaries',
            'crm.leads.view_marketing',
            'crm.leads.convert',
            'crm.leads.export',
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
