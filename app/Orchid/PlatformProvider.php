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
            Menu::make('Dashboard')
                ->icon('bs.speedometer2')
                ->title('Navigation')
                ->route(config('platform.index')),

            Menu::make('Homepage')
                ->icon('bs.layout-text-window-reverse')
                ->route('platform.content.home')
                ->permission('platform.content.home'),

            Menu::make('Branches')
                ->icon('bs.building')
                ->route('platform.operations.branches')
                ->permission('platform.operations.branches')
                ->title('Operations'),

            Menu::make('Instructors')
                ->icon('bs.person-badge')
                ->route('platform.operations.instructors')
                ->permission('platform.operations.instructors'),

            Menu::make('Groups')
                ->icon('bs.collection')
                ->route('platform.operations.groups')
                ->permission('platform.operations.groups'),

            Menu::make('Student CRM')
                ->icon('bs.person-lines-fill')
                ->route('platform.crm.students')
                ->permission('platform.crm.students'),

            Menu::make('LMS Programs')
                ->icon('bs.mortarboard')
                ->route('platform.lms.programs')
                ->permission('platform.lms.programs'),

            Menu::make('Schedule')
                ->icon('bs.calendar-week')
                ->route('platform.schedule.lessons')
                ->permission('platform.schedule.lessons'),

            Menu::make('Fleet')
                ->icon('bs.car-front')
                ->route('platform.fleet.vehicles')
                ->permission('platform.fleet.vehicles'),

            Menu::make('Exams')
                ->icon('bs.clipboard-check')
                ->route('platform.exams')
                ->permission('platform.exams'),

            Menu::make('Payments')
                ->icon('bs.credit-card')
                ->route('platform.finance.payments')
                ->permission('platform.finance.payments'),

            Menu::make('Documents')
                ->icon('bs.folder2-open')
                ->route('platform.documents')
                ->permission('platform.documents'),

            Menu::make('Campaigns')
                ->icon('bs.megaphone')
                ->route('platform.marketing.campaigns')
                ->permission('platform.marketing.campaigns')
                ->title('Marketing'),

            Menu::make('Leads')
                ->icon('bs.funnel')
                ->route('platform.marketing.leads')
                ->permission('platform.marketing.leads'),

            Menu::make('View Website')
                ->icon('bs.box-arrow-up-right')
                ->route('site.home')
                ->target('_blank')
                ->divider(),

            Menu::make(__('Users'))
                ->icon('bs.people')
                ->route('platform.systems.users')
                ->permission('platform.systems.users')
                ->title(__('Access Controls')),

            Menu::make(__('Roles'))
                ->icon('bs.shield')
                ->route('platform.systems.roles')
                ->permission('platform.systems.roles')
                ->divider(),

            Menu::make('Documentation')
                ->title('Docs')
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
            ItemPermission::group('Content')
                ->addPermission('platform.content.home', 'Homepage')
                ->addPermission('platform.lms.programs', 'LMS programs')
                ->addPermission('platform.documents', 'Documents'),

            ItemPermission::group('Operations')
                ->addPermission('platform.operations.branches', 'Branches')
                ->addPermission('platform.operations.instructors', 'Instructors')
                ->addPermission('platform.operations.groups', 'Training groups')
                ->addPermission('platform.crm.students', 'Student CRM')
                ->addPermission('platform.schedule.lessons', 'Schedule')
                ->addPermission('platform.fleet.vehicles', 'Fleet')
                ->addPermission('platform.exams', 'Exams')
                ->addPermission('platform.finance.payments', 'Payments'),

            ItemPermission::group('Marketing')
                ->addPermission('platform.marketing.campaigns', 'Campaigns')
                ->addPermission('platform.marketing.leads', 'Leads'),

            ItemPermission::group(__('System'))
                ->addPermission('platform.systems.roles', __('Roles'))
                ->addPermission('platform.systems.users', __('Users')),
        ];
    }
}
