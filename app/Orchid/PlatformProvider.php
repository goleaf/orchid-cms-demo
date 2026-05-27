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

            Menu::make(tkey('menu.marketing.campaigns'))
                ->icon('bs.megaphone')
                ->route('platform.marketing.campaigns')
                ->permission('platform.marketing.campaigns')
                ->title(tkey('menu.marketing')),

            Menu::make(tkey('menu.marketing.pipeline'))
                ->icon('bs.kanban')
                ->route('platform.marketing.pipeline')
                ->permission('platform.marketing.pipeline'),

            Menu::make(tkey('menu.marketing.leads'))
                ->icon('bs.funnel')
                ->route('platform.marketing.leads')
                ->permission('platform.marketing.leads'),

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
                ->addPermission('platform.marketing.leads', tkey('permissions.marketing.leads')),

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
