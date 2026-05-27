<?php

declare(strict_types=1);

namespace App\Orchid;

use Orchid\Platform\Dashboard;
use Orchid\Platform\ItemPermission;
use Orchid\Platform\OrchidServiceProvider;
use Orchid\Screen\Actions\Menu;

class PlatformProvider extends OrchidServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(Dashboard $dashboard): void
    {
        parent::boot($dashboard);

        // ...
    }

    /**
     * Register the application menu.
     *
     * @return Menu[]
     */
    public function menu(): array
    {
        return [
            Menu::make(tkey('menu.dashboard'))
                ->icon('bs.speedometer2')
                ->title(tkey('menu.navigation'))
                ->route(config('platform.index')),

            Menu::make(tkey('menu.content.home'))
                ->icon('bs.layout-text-window-reverse')
                ->route('platform.content.home')
                ->permission('platform.content.home'),

            Menu::make(tkey('menu.website.courses'))
                ->icon('bs.book')
                ->route('platform.website.courses')
                ->permission('website.manage_courses')
                ->title(tkey('menu.website')),

            Menu::make(tkey('menu.website.pricing'))
                ->icon('bs.cash-coin')
                ->route('platform.website.pricing')
                ->permission('website.manage_courses'),

            Menu::make(tkey('menu.website.branches'))
                ->icon('bs.building')
                ->route('platform.website.branches')
                ->permission('website.manage_branches'),

            Menu::make(tkey('menu.website.groups'))
                ->icon('bs.calendar-event')
                ->route('platform.website.groups')
                ->permission('website.manage_groups'),

            Menu::make(tkey('menu.website.leads'))
                ->icon('bs.inbox')
                ->route('platform.website.leads')
                ->permission('website.view_leads'),

            Menu::make(tkey('menu.website.settings'))
                ->icon('bs.sliders')
                ->route('platform.website.settings')
                ->permission('website.manage_settings'),

            Menu::make(tkey('menu.operations.branches'))
                ->icon('bs.building')
                ->route('platform.operations.branches')
                ->permission('platform.operations.branches')
                ->title(tkey('menu.operations')),

            Menu::make(tkey('menu.operations.instructors'))
                ->icon('bs.person-badge')
                ->route('platform.operations.instructors')
                ->permission('platform.operations.instructors'),

            Menu::make(tkey('menu.operations.groups'))
                ->icon('bs.collection')
                ->route('platform.operations.groups')
                ->permission('platform.operations.groups'),

            Menu::make(tkey('menu.crm.students'))
                ->icon('bs.person-lines-fill')
                ->route('platform.crm.students')
                ->permission('platform.crm.students'),

            Menu::make(tkey('menu.lms.programs'))
                ->icon('bs.mortarboard')
                ->route('platform.lms.programs')
                ->permission('platform.lms.programs'),

            Menu::make(tkey('menu.schedule.lessons'))
                ->icon('bs.calendar-week')
                ->route('platform.schedule.lessons')
                ->permission('platform.schedule.lessons'),

            Menu::make(tkey('menu.fleet.vehicles'))
                ->icon('bs.car-front')
                ->route('platform.fleet.vehicles')
                ->permission('platform.fleet.vehicles'),

            Menu::make(tkey('menu.exams'))
                ->icon('bs.clipboard-check')
                ->route('platform.exams')
                ->permission('platform.exams'),

            Menu::make(tkey('menu.finance.payments'))
                ->icon('bs.credit-card')
                ->route('platform.finance.payments')
                ->permission('platform.finance.payments'),

            Menu::make(tkey('menu.documents'))
                ->icon('bs.folder2-open')
                ->route('platform.documents')
                ->permission('platform.documents'),

            Menu::make(tkey('menu.crm.leads'))
                ->icon('bs.funnel')
                ->route('platform.marketing.leads')
                ->permission('crm.leads.view')
                ->title(tkey('menu.crm')),

            Menu::make(tkey('menu.crm.new_leads'))
                ->icon('bs.inbox')
                ->route('platform.marketing.leads', ['status' => 'new'])
                ->permission('crm.leads.view'),

            Menu::make(tkey('menu.crm.my_leads'))
                ->icon('bs.person-check')
                ->route('platform.marketing.leads', ['segment' => 'my'])
                ->permission('crm.leads.view'),

            Menu::make(tkey('menu.crm.overdue_tasks'))
                ->icon('bs.exclamation-triangle')
                ->route('platform.marketing.leads', ['overdue' => '1'])
                ->permission('crm.leads.view'),

            Menu::make(tkey('menu.crm.pipeline'))
                ->icon('bs.kanban')
                ->route('platform.marketing.pipeline')
                ->permission('crm.leads.view'),

            Menu::make(tkey('menu.crm.tasks'))
                ->icon('bs.check2-square')
                ->route('platform.crm.tasks')
                ->permission('crm.leads.manage_tasks'),

            Menu::make(tkey('menu.crm.statuses'))
                ->icon('bs.ui-checks-grid')
                ->route('platform.crm.dictionaries', 'statuses')
                ->permission('crm.leads.manage_dictionaries'),

            Menu::make(tkey('menu.crm.sources'))
                ->icon('bs.signpost-split')
                ->route('platform.crm.dictionaries', 'sources')
                ->permission('crm.leads.manage_dictionaries'),

            Menu::make(tkey('menu.crm.lost_reasons'))
                ->icon('bs.x-octagon')
                ->route('platform.crm.dictionaries', 'lost-reasons')
                ->permission('crm.leads.manage_dictionaries'),

            Menu::make(tkey('menu.crm.tags'))
                ->icon('bs.tags')
                ->route('platform.crm.dictionaries', 'tags')
                ->permission('crm.leads.manage_dictionaries'),

            Menu::make(tkey('menu.marketing.campaigns'))
                ->icon('bs.megaphone')
                ->route('platform.marketing.campaigns')
                ->permission('platform.marketing.campaigns')
                ->title(tkey('menu.marketing')),

            Menu::make(tkey('menu.marketing.templates'))
                ->icon('bs.chat-square-text')
                ->route('platform.marketing.templates')
                ->permission('platform.marketing.templates'),

            Menu::make(tkey('menu.website.view'))
                ->icon('bs.box-arrow-up-right')
                ->route('site.home')
                ->target('_blank')
                ->divider(),

            Menu::make(tkey('menu.settings'))
                ->icon('bs.gear')
                ->permission('system.languages.view')
                ->title(tkey('menu.settings'))
                ->list([
                    Menu::make(tkey('menu.settings.languages'))
                        ->icon('bs.translate')
                        ->route('platform.system.languages')
                        ->permission('system.languages.view'),
                    Menu::make(tkey('menu.settings.translations'))
                        ->icon('bs.body-text')
                        ->route('platform.system.translations')
                        ->permission('system.translations.view'),
                ]),

            Menu::make(tkey('menu.system.users'))
                ->icon('bs.people')
                ->route('platform.systems.users')
                ->permission('platform.systems.users')
                ->title(tkey('menu.access_controls')),

            Menu::make(tkey('menu.system.roles'))
                ->icon('bs.shield')
                ->route('platform.systems.roles')
                ->permission('platform.systems.roles')
                ->divider(),

            Menu::make(tkey('menu.docs'))
                ->title(tkey('menu.docs'))
                ->icon('bs.box-arrow-up-right')
                ->url('https://orchid.software/en/docs')
                ->target('_blank'),
        ];
    }

    /**
     * Register permissions for the application.
     *
     * @return ItemPermission[]
     */
    public function permissions(): array
    {
        return [
            ItemPermission::group(tkey('permissions.groups.content'))
                ->addPermission('platform.content.home', tkey('permissions.content.home'))
                ->addPermission('platform.lms.programs', tkey('permissions.lms.programs'))
                ->addPermission('platform.documents', tkey('permissions.documents')),

            ItemPermission::group(tkey('permissions.groups.website'))
                ->addPermission('website.view', tkey('permissions.website.view'))
                ->addPermission('website.manage_pages', tkey('permissions.website.manage_pages'))
                ->addPermission('website.manage_courses', tkey('permissions.website.manage_courses'))
                ->addPermission('website.manage_branches', tkey('permissions.website.manage_branches'))
                ->addPermission('website.manage_groups', tkey('permissions.website.manage_groups'))
                ->addPermission('website.manage_settings', tkey('permissions.website.manage_settings'))
                ->addPermission('website.view_leads', tkey('permissions.website.view_leads'))
                ->addPermission('website.update_leads', tkey('permissions.website.update_leads'))
                ->addPermission('website.view_marketing', tkey('permissions.website.view_marketing')),

            ItemPermission::group(tkey('permissions.groups.operations'))
                ->addPermission('platform.operations.branches', tkey('permissions.operations.branches'))
                ->addPermission('platform.operations.instructors', tkey('permissions.operations.instructors'))
                ->addPermission('platform.operations.groups', tkey('permissions.operations.groups'))
                ->addPermission('platform.crm.students', tkey('permissions.crm.students'))
                ->addPermission('platform.schedule.lessons', tkey('permissions.schedule.lessons'))
                ->addPermission('platform.fleet.vehicles', tkey('permissions.fleet.vehicles'))
                ->addPermission('platform.exams', tkey('permissions.exams'))
                ->addPermission('platform.finance.payments', tkey('permissions.finance.payments')),

            ItemPermission::group(tkey('permissions.groups.marketing'))
                ->addPermission('platform.marketing.campaigns', tkey('permissions.marketing.campaigns'))
                ->addPermission('platform.marketing.pipeline', tkey('permissions.marketing.pipeline'))
                ->addPermission('platform.marketing.leads', tkey('permissions.marketing.leads'))
                ->addPermission('platform.marketing.templates', tkey('permissions.marketing.templates')),

            ItemPermission::group(tkey('permissions.groups.crm'))
                ->addPermission('crm.leads.view', tkey('permissions.crm.leads.view'))
                ->addPermission('crm.leads.create', tkey('permissions.crm.leads.create'))
                ->addPermission('crm.leads.update', tkey('permissions.crm.leads.update'))
                ->addPermission('crm.leads.delete', tkey('permissions.crm.leads.delete'))
                ->addPermission('crm.leads.assign', tkey('permissions.crm.leads.assign'))
                ->addPermission('crm.leads.change_status', tkey('permissions.crm.leads.change_status'))
                ->addPermission('crm.leads.manage_tasks', tkey('permissions.crm.leads.manage_tasks'))
                ->addPermission('crm.leads.manage_dictionaries', tkey('permissions.crm.leads.manage_dictionaries'))
                ->addPermission('crm.leads.view_marketing', tkey('permissions.crm.leads.view_marketing'))
                ->addPermission('crm.leads.convert', tkey('permissions.crm.leads.convert'))
                ->addPermission('crm.leads.export', tkey('permissions.crm.leads.export')),

            ItemPermission::group(tkey('permissions.groups.system'))
                ->addPermission('platform.systems.roles', tkey('permissions.system.roles'))
                ->addPermission('platform.systems.users', tkey('permissions.system.users'))
                ->addPermission('system.languages.view', tkey('permissions.system.languages.view'))
                ->addPermission('system.languages.create', tkey('permissions.system.languages.create'))
                ->addPermission('system.languages.update', tkey('permissions.system.languages.update'))
                ->addPermission('system.languages.delete', tkey('permissions.system.languages.delete'))
                ->addPermission('system.translations.view', tkey('permissions.system.translations.view'))
                ->addPermission('system.translations.update', tkey('permissions.system.translations.update'))
                ->addPermission('system.translations.export', tkey('permissions.system.translations.export'))
                ->addPermission('system.translations.import', tkey('permissions.system.translations.import')),
        ];
    }
}
