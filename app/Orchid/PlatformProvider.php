<?php

declare(strict_types=1);

namespace App\Orchid;

use App\Models\CommunicationReminder;
use App\Models\MarketingLead;
use App\Models\MarketingLeadTask;
use App\Models\Student;
use App\Models\StudentTask;
use App\Models\TrainingGroup;
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

            Menu::make(tkey('menu.analytics.dashboard'))
                ->icon('bs.graph-up-arrow')
                ->route('platform.analytics.dashboard')
                ->permission('analytics.dashboard.view')
                ->title(tkey('menu.analytics')),

            Menu::make(tkey('menu.content.home'))
                ->icon('bs.layout-text-window-reverse')
                ->route('platform.content.home')
                ->permission('platform.content.home'),

            Menu::make(tkey('menu.website.pages'))
                ->icon('bs.file-earmark-richtext')
                ->route('platform.website.pages')
                ->permission('website.manage_pages')
                ->title(tkey('menu.website')),

            Menu::make(tkey('menu.website.courses'))
                ->icon('bs.book')
                ->route('platform.website.courses')
                ->permission('website.manage_courses'),

            Menu::make(tkey('menu.website.course_categories'))
                ->icon('bs.collection')
                ->route('platform.website.course-categories')
                ->permission('website.manage_course_categories'),

            Menu::make(tkey('menu.website.pricing'))
                ->icon('bs.cash-coin')
                ->route('platform.website.pricing')
                ->permission('website.manage_pricing'),

            Menu::make(tkey('menu.website.branches'))
                ->icon('bs.building')
                ->route('platform.website.branches')
                ->permission('website.manage_branches'),

            Menu::make(tkey('menu.website.groups'))
                ->icon('bs.calendar-event')
                ->route('platform.website.groups')
                ->permission('website.manage_groups'),

            Menu::make(tkey('menu.website.faq'))
                ->icon('bs.question-circle')
                ->route('platform.website.faq')
                ->permission('website.manage_faq'),

            Menu::make(tkey('menu.website.testimonials'))
                ->icon('bs.star')
                ->route('platform.website.testimonials')
                ->permission('website.manage_testimonials'),

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

            Menu::make(tkey('menu.lms.programs'))
                ->icon('bs.mortarboard')
                ->route('platform.lms.programs')
                ->permission('platform.lms.programs'),

            Menu::make(tkey('menu.education.groups'))
                ->icon('bs.people-fill')
                ->route('platform.education.groups')
                ->permission('education.groups.view')
                ->title(tkey('menu.education'))
                ->badge(fn (): int => TrainingGroup::query()->count()),

            Menu::make(tkey('menu.education.groups.recruiting'))
                ->icon('bs.person-plus')
                ->route('platform.education.groups', ['segment' => 'recruiting'])
                ->permission('education.groups.view')
                ->badge(fn (): int => TrainingGroup::query()->recruiting()->count()),

            Menu::make(tkey('menu.education.groups.scheduled'))
                ->icon('bs.calendar-event')
                ->route('platform.education.groups', ['segment' => 'scheduled'])
                ->permission('education.groups.view'),

            Menu::make(tkey('menu.education.groups.active'))
                ->icon('bs.play-circle')
                ->route('platform.education.groups', ['segment' => 'active'])
                ->permission('education.groups.view')
                ->badge(fn (): int => TrainingGroup::query()->active()->count()),

            Menu::make(tkey('menu.education.groups.completed'))
                ->icon('bs.check2-circle')
                ->route('platform.education.groups', ['segment' => 'completed'])
                ->permission('education.groups.view'),

            Menu::make(tkey('menu.education.groups.cancelled'))
                ->icon('bs.x-circle')
                ->route('platform.education.groups', ['segment' => 'cancelled'])
                ->permission('education.groups.view'),

            Menu::make(tkey('menu.education.groups.archived'))
                ->icon('bs.archive')
                ->route('platform.education.groups', ['segment' => 'archived'])
                ->permission('education.groups.view'),

            Menu::make(tkey('menu.education.programs'))
                ->icon('bs.journal-text')
                ->route('platform.education.programs')
                ->permission('education.programs.view'),

            Menu::make(tkey('menu.education.statuses'))
                ->icon('bs.ui-checks-grid')
                ->route('platform.education.group-statuses')
                ->permission('education.groups.manage_statuses'),

            Menu::make(tkey('menu.schedule.lessons'))
                ->icon('bs.calendar-week')
                ->route('platform.schedule.lessons')
                ->permission('platform.schedule.lessons'),

            Menu::make(tkey('menu.fleet.vehicles'))
                ->icon('bs.car-front')
                ->route('platform.fleet.vehicles')
                ->permission('platform.fleet.vehicles'),

            Menu::make(tkey('menu.exams.sessions'))
                ->icon('bs.calendar-check')
                ->route('platform.exams.sessions')
                ->permission('exams.sessions.view')
                ->title(tkey('menu.exams')),

            Menu::make(tkey('menu.exams.internal'))
                ->icon('bs.clipboard-check')
                ->route('platform.exams.sessions', ['type_scope' => 'internal'])
                ->permission('exams.sessions.view'),

            Menu::make(tkey('menu.exams.official'))
                ->icon('bs.building-check')
                ->route('platform.exams.sessions', ['type_scope' => 'official'])
                ->permission('exams.sessions.view'),

            Menu::make(tkey('menu.exams.attempts'))
                ->icon('bs.list-check')
                ->route('platform.exams.attempts')
                ->permission('exams.attempts.view'),

            Menu::make(tkey('menu.exams.results'))
                ->icon('bs.patch-check')
                ->route('platform.exams.results')
                ->permission('exams.results.view'),

            Menu::make(tkey('menu.exams.retakes'))
                ->icon('bs.arrow-counterclockwise')
                ->route('platform.exams.retakes')
                ->permission('exams.retakes.view'),

            Menu::make(tkey('menu.exams.admissions'))
                ->icon('bs.ui-checks')
                ->route('platform.exams.admissions')
                ->permission('exams.admissions.check'),

            Menu::make(tkey('menu.exams.settings'))
                ->icon('bs.sliders')
                ->permission('exams.dictionaries.manage')
                ->list([
                    Menu::make(tkey('exams.dictionaries.types.title'))
                        ->icon('bs.tags')
                        ->route('platform.exams.types')
                        ->permission('exams.dictionaries.manage'),
                    Menu::make(tkey('exams.dictionaries.statuses.title'))
                        ->icon('bs.ui-checks-grid')
                        ->route('platform.exams.statuses')
                        ->permission('exams.dictionaries.manage'),
                    Menu::make(tkey('exams.dictionaries.attempt_statuses.title'))
                        ->icon('bs.check2-square')
                        ->route('platform.exams.attempt-statuses')
                        ->permission('exams.dictionaries.manage'),
                    Menu::make(tkey('exams.dictionaries.result_statuses.title'))
                        ->icon('bs.clipboard2-data')
                        ->route('platform.exams.result-statuses')
                        ->permission('exams.dictionaries.manage'),
                ]),

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
                ->route('platform.crm.leads')
                ->permission('crm.leads.view')
                ->title(tkey('menu.crm')),

            Menu::make(tkey('menu.crm.new_leads'))
                ->icon('bs.inbox')
                ->route('platform.crm.leads', ['segment' => 'new'])
                ->permission('crm.leads.view')
                ->badge(fn (): int => MarketingLead::query()->new()->count()),

            Menu::make(tkey('menu.crm.my_leads'))
                ->icon('bs.person-check')
                ->route('platform.crm.leads', ['segment' => 'my'])
                ->permission('crm.leads.view'),

            Menu::make(tkey('menu.crm.unassigned'))
                ->icon('bs.person-dash')
                ->route('platform.crm.leads', ['segment' => 'unassigned'])
                ->permission('crm.leads.view')
                ->badge(fn (): int => MarketingLead::query()->open()->unassigned()->count()),

            Menu::make(tkey('menu.crm.overdue_tasks'))
                ->icon('bs.exclamation-triangle')
                ->route('platform.crm.tasks', ['segment' => 'overdue'])
                ->permission('crm.leads.manage_tasks')
                ->badge(fn (): int => MarketingLeadTask::query()->overdue()->count()),

            Menu::make(tkey('menu.crm.pipeline'))
                ->icon('bs.kanban')
                ->route('platform.crm.pipeline')
                ->permission('crm.pipeline.view'),

            Menu::make(tkey('menu.crm.tasks'))
                ->icon('bs.check2-square')
                ->route('platform.crm.tasks')
                ->permission('crm.leads.manage_tasks'),

            Menu::make(tkey('menu.crm.statuses'))
                ->icon('bs.ui-checks-grid')
                ->route('platform.crm.statuses')
                ->permission('crm.leads.manage_dictionaries'),

            Menu::make(tkey('menu.crm.sources'))
                ->icon('bs.signpost-split')
                ->route('platform.crm.sources')
                ->permission('crm.leads.manage_dictionaries'),

            Menu::make(tkey('menu.crm.lost_reasons'))
                ->icon('bs.x-octagon')
                ->route('platform.crm.lost-reasons')
                ->permission('crm.leads.manage_dictionaries'),

            Menu::make(tkey('menu.crm.tags'))
                ->icon('bs.tags')
                ->route('platform.crm.tags')
                ->permission(['crm.leads.manage_dictionaries', 'crm.leads.manage_tags']),

            Menu::make(tkey('menu.students.all'))
                ->icon('bs.person-lines-fill')
                ->route('platform.students')
                ->permission('students.view')
                ->title(tkey('menu.students')),

            Menu::make(tkey('menu.students.active'))
                ->icon('bs.person-check')
                ->route('platform.students', ['segment' => 'active'])
                ->permission('students.view')
                ->badge(fn (): int => Student::query()->active()->count()),

            Menu::make(tkey('menu.students.waiting_documents'))
                ->icon('bs.file-earmark-text')
                ->route('platform.students', ['segment' => 'waiting_documents'])
                ->permission('students.view'),

            Menu::make(tkey('menu.students.waiting_payment'))
                ->icon('bs.cash-coin')
                ->route('platform.students', ['segment' => 'waiting_payment'])
                ->permission('students.view'),

            Menu::make(tkey('menu.students.waiting_start'))
                ->icon('bs.calendar-event')
                ->route('platform.students', ['segment' => 'waiting_start'])
                ->permission('students.view'),

            Menu::make(tkey('menu.students.without_group'))
                ->icon('bs.people')
                ->route('platform.students', ['segment' => 'without_group'])
                ->permission('students.view'),

            Menu::make(tkey('menu.students.archived'))
                ->icon('bs.archive')
                ->route('platform.students', ['segment' => 'archived'])
                ->permission('students.view'),

            Menu::make(tkey('menu.students.tasks'))
                ->icon('bs.check2-square')
                ->route('platform.students.tasks')
                ->permission('students.manage_tasks')
                ->badge(fn (): int => StudentTask::query()->overdue()->count()),

            Menu::make(tkey('menu.students.statuses'))
                ->icon('bs.ui-checks-grid')
                ->route('platform.students.statuses')
                ->permission('students.manage_statuses'),

            Menu::make(tkey('menu.students.enrollment_statuses'))
                ->icon('bs.list-check')
                ->route('platform.students.enrollment-statuses')
                ->permission('students.manage_statuses'),

            Menu::make(tkey('menu.communications.channels'))
                ->icon('bs.bell')
                ->route('platform.communications.channels')
                ->permission('communications.channels.view')
                ->title(tkey('menu.communications')),

            Menu::make(tkey('menu.communications.templates'))
                ->icon('bs.chat-square-text')
                ->route('platform.communications.templates')
                ->permission('communications.templates.view'),

            Menu::make(tkey('menu.communications.reminders'))
                ->icon('bs.alarm')
                ->route('platform.communications.reminders')
                ->permission('communications.reminders.view')
                ->badge(fn (): int => CommunicationReminder::query()->due()->count()),

            Menu::make(tkey('menu.communications.delivery_logs'))
                ->icon('bs.list-check')
                ->route('platform.communications.delivery-logs')
                ->permission('communications.delivery_logs.view'),

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

            ItemPermission::group(tkey('permissions.groups.analytics'))
                ->addPermission('analytics.dashboard.view', tkey('permissions.analytics.dashboard.view'))
                ->addPermission('analytics.reports.manage', tkey('permissions.analytics.reports.manage'))
                ->addPermission('analytics.reports.run', tkey('permissions.analytics.reports.run'))
                ->addPermission('analytics.reports.export', tkey('permissions.analytics.reports.export'))
                ->addPermission('analytics.kpis.manage', tkey('permissions.analytics.kpis.manage'))
                ->addPermission('analytics.kpi_targets.manage', tkey('permissions.analytics.kpi_targets.manage'))
                ->addPermission('analytics.preferences.manage', tkey('permissions.analytics.preferences.manage'))
                ->addPermission('analytics.cache.view', tkey('permissions.analytics.cache.view')),

            ItemPermission::group(tkey('permissions.groups.website'))
                ->addPermission('website.view', tkey('permissions.website.view'))
                ->addPermission('website.manage_pages', tkey('permissions.website.manage_pages'))
                ->addPermission('website.manage_courses', tkey('permissions.website.manage_courses'))
                ->addPermission('website.manage_course_categories', tkey('permissions.website.manage_course_categories'))
                ->addPermission('website.manage_pricing', tkey('permissions.website.manage_pricing'))
                ->addPermission('website.manage_branches', tkey('permissions.website.manage_branches'))
                ->addPermission('website.manage_groups', tkey('permissions.website.manage_groups'))
                ->addPermission('website.manage_faq', tkey('permissions.website.manage_faq'))
                ->addPermission('website.manage_testimonials', tkey('permissions.website.manage_testimonials'))
                ->addPermission('website.manage_settings', tkey('permissions.website.manage_settings'))
                ->addPermission('website.view_leads', tkey('permissions.website.view_leads'))
                ->addPermission('website.update_leads', tkey('permissions.website.update_leads'))
                ->addPermission('website.view_marketing', tkey('permissions.website.view_marketing'))
                ->addPermission('website.preview', tkey('permissions.website.preview')),

            ItemPermission::group(tkey('permissions.groups.operations'))
                ->addPermission('platform.operations.branches', tkey('permissions.operations.branches'))
                ->addPermission('platform.operations.instructors', tkey('permissions.operations.instructors'))
                ->addPermission('platform.operations.groups', tkey('permissions.operations.groups'))
                ->addPermission('platform.crm.students', tkey('permissions.crm.students'))
                ->addPermission('platform.schedule.lessons', tkey('permissions.schedule.lessons'))
                ->addPermission('platform.fleet.vehicles', tkey('permissions.fleet.vehicles'))
                ->addPermission('platform.exams', tkey('permissions.exams'))
                ->addPermission('exams.view', tkey('permissions.exams.view'))
                ->addPermission('exams.manage_admissions', tkey('permissions.exams.manage_admissions'))
                ->addPermission('exams.manage_sessions', tkey('permissions.exams.manage_sessions'))
                ->addPermission('exams.record_results', tkey('permissions.exams.record_results'))
                ->addPermission('exams.schedule_retakes', tkey('permissions.exams.schedule_retakes'))
                ->addPermission('exams.view_activities', tkey('permissions.exams.view_activities'))
                ->addPermission('platform.finance.payments', tkey('permissions.finance.payments')),

            ItemPermission::group(tkey('permissions.groups.exams'))
                ->addPermission('exams.sessions.view', tkey('permissions.exams.sessions.view'))
                ->addPermission('exams.sessions.create', tkey('permissions.exams.sessions.create'))
                ->addPermission('exams.sessions.update', tkey('permissions.exams.sessions.update'))
                ->addPermission('exams.sessions.cancel', tkey('permissions.exams.sessions.cancel'))
                ->addPermission('exams.admissions.check', tkey('permissions.exams.admissions.check'))
                ->addPermission('exams.admissions.approve', tkey('permissions.exams.admissions.approve'))
                ->addPermission('exams.admissions.block', tkey('permissions.exams.admissions.block'))
                ->addPermission('exams.attempts.view', tkey('permissions.exams.attempts.view'))
                ->addPermission('exams.attempts.create', tkey('permissions.exams.attempts.create'))
                ->addPermission('exams.attempts.start', tkey('permissions.exams.attempts.start'))
                ->addPermission('exams.attempts.complete', tkey('permissions.exams.attempts.complete'))
                ->addPermission('exams.attempts.cancel', tkey('permissions.exams.attempts.cancel'))
                ->addPermission('exams.results.view', tkey('permissions.exams.results.view'))
                ->addPermission('exams.results.record', tkey('permissions.exams.results.record'))
                ->addPermission('exams.results.update', tkey('permissions.exams.results.update'))
                ->addPermission('exams.retakes.view', tkey('permissions.exams.retakes.view'))
                ->addPermission('exams.retakes.create', tkey('permissions.exams.retakes.create'))
                ->addPermission('exams.retakes.schedule', tkey('permissions.exams.retakes.schedule'))
                ->addPermission('exams.dictionaries.manage', tkey('permissions.exams.dictionaries.manage'))
                ->addPermission('exams.export', tkey('permissions.exams.export')),

            ItemPermission::group(tkey('permissions.groups.education'))
                ->addPermission('education.groups.view', tkey('permissions.education.groups.view'))
                ->addPermission('education.groups.create', tkey('permissions.education.groups.create'))
                ->addPermission('education.groups.update', tkey('permissions.education.groups.update'))
                ->addPermission('education.groups.archive', tkey('permissions.education.groups.archive'))
                ->addPermission('education.groups.delete', tkey('permissions.education.groups.delete'))
                ->addPermission('education.groups.change_status', tkey('permissions.education.groups.change_status'))
                ->addPermission('education.groups.override_status_transition', tkey('permissions.education.groups.override_status_transition'))
                ->addPermission('education.groups.manage_students', tkey('permissions.education.groups.manage_students'))
                ->addPermission('education.groups.manage_schedule_patterns', tkey('permissions.education.groups.manage_schedule_patterns'))
                ->addPermission('education.groups.manage_statuses', tkey('permissions.education.groups.manage_statuses'))
                ->addPermission('education.groups.manage_public_visibility', tkey('permissions.education.groups.manage_public_visibility'))
                ->addPermission('education.groups.manage_learning_program', tkey('permissions.education.groups.manage_learning_program'))
                ->addPermission('education.groups.export', tkey('permissions.education.groups.export'))
                ->addPermission('education.programs.view', tkey('permissions.education.programs.view'))
                ->addPermission('education.programs.create', tkey('permissions.education.programs.create'))
                ->addPermission('education.programs.update', tkey('permissions.education.programs.update'))
                ->addPermission('education.programs.delete', tkey('permissions.education.programs.delete'))
                ->addPermission('education.programs.manage_modules', tkey('permissions.education.programs.manage_modules'))
                ->addPermission('education.programs.manage_topics', tkey('permissions.education.programs.manage_topics'))
                ->addPermission('education.manage_statuses', tkey('permissions.education.groups.manage_statuses'))
                ->addPermission('education.manage_memberships', tkey('permissions.education.groups.manage_students'))
                ->addPermission('education.manage_schedule_patterns', tkey('permissions.education.groups.manage_schedule_patterns'))
                ->addPermission('education.manage_topics', tkey('permissions.education.programs.manage_topics'))
                ->addPermission('education.view_activities', tkey('permissions.education.groups.view')),

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
                ->addPermission('crm.leads.archive', tkey('permissions.crm.leads.archive'))
                ->addPermission('crm.leads.assign', tkey('permissions.crm.leads.assign'))
                ->addPermission('crm.leads.change_status', tkey('permissions.crm.leads.change_status'))
                ->addPermission('crm.leads.override_status_transition', tkey('permissions.crm.leads.override_status_transition'))
                ->addPermission('crm.leads.manage_tasks', tkey('permissions.crm.leads.manage_tasks'))
                ->addPermission('crm.leads.manage_dictionaries', tkey('permissions.crm.leads.manage_dictionaries'))
                ->addPermission('crm.leads.manage_tags', tkey('permissions.crm.leads.manage_tags'))
                ->addPermission('crm.leads.view_marketing', tkey('permissions.crm.leads.view_marketing'))
                ->addPermission('crm.leads.convert', tkey('permissions.crm.leads.convert'))
                ->addPermission('crm.leads.export', tkey('permissions.crm.leads.export'))
                ->addPermission('crm.pipeline.view', tkey('permissions.crm.pipeline.view')),

            ItemPermission::group(tkey('permissions.groups.students'))
                ->addPermission('students.view', tkey('permissions.students.view'))
                ->addPermission('students.create', tkey('permissions.students.create'))
                ->addPermission('students.update', tkey('permissions.students.update'))
                ->addPermission('students.archive', tkey('permissions.students.archive'))
                ->addPermission('students.delete', tkey('permissions.students.delete'))
                ->addPermission('students.change_status', tkey('permissions.students.change_status'))
                ->addPermission('students.override_status_transition', tkey('permissions.students.override_status_transition'))
                ->addPermission('students.convert_from_lead', tkey('permissions.students.convert_from_lead'))
                ->addPermission('students.manage_enrollments', tkey('permissions.students.manage_enrollments'))
                ->addPermission('students.enrollments.change_status', tkey('permissions.students.enrollments.change_status'))
                ->addPermission('students.enrollments.override_status_transition', tkey('permissions.students.enrollments.override_status_transition'))
                ->addPermission('students.manage_tasks', tkey('permissions.students.manage_tasks'))
                ->addPermission('students.view_crm_source', tkey('permissions.students.view_crm_source'))
                ->addPermission('students.view_marketing', tkey('permissions.students.view_marketing'))
                ->addPermission('students.manage_statuses', tkey('permissions.students.manage_statuses'))
                ->addPermission('students.export', tkey('permissions.students.export')),

            ItemPermission::group(tkey('permissions.groups.communications'))
                ->addPermission('communications.channels.view', tkey('permissions.communications.channels.view'))
                ->addPermission('communications.channels.manage', tkey('permissions.communications.channels.manage'))
                ->addPermission('communications.templates.view', tkey('permissions.communications.templates.view'))
                ->addPermission('communications.templates.manage', tkey('permissions.communications.templates.manage'))
                ->addPermission('communications.reminders.view', tkey('permissions.communications.reminders.view'))
                ->addPermission('communications.reminders.manage', tkey('permissions.communications.reminders.manage'))
                ->addPermission('communications.delivery_logs.view', tkey('permissions.communications.delivery_logs.view'))
                ->addPermission('communications.preferences.manage', tkey('permissions.communications.preferences.manage'))
                ->addPermission('communications.student_history.manage', tkey('permissions.communications.student_history.manage'))
                ->addPermission('communications.lead_history.view', tkey('permissions.communications.lead_history.view')),

            ItemPermission::group(tkey('permissions.groups.notifications'))
                ->addPermission('notifications.messages.view', tkey('permissions.notifications.messages.view'))
                ->addPermission('notifications.messages.create', tkey('permissions.notifications.messages.create'))
                ->addPermission('notifications.messages.send', tkey('permissions.notifications.messages.send'))
                ->addPermission('notifications.messages.cancel', tkey('permissions.notifications.messages.cancel'))
                ->addPermission('notifications.messages.retry', tkey('permissions.notifications.messages.retry'))
                ->addPermission('notifications.templates.view', tkey('permissions.notifications.templates.view'))
                ->addPermission('notifications.templates.create', tkey('permissions.notifications.templates.create'))
                ->addPermission('notifications.templates.update', tkey('permissions.notifications.templates.update'))
                ->addPermission('notifications.templates.publish', tkey('permissions.notifications.templates.publish'))
                ->addPermission('notifications.reminders.view', tkey('permissions.notifications.reminders.view'))
                ->addPermission('notifications.reminders.manage', tkey('permissions.notifications.reminders.manage'))
                ->addPermission('notifications.reminders.process', tkey('permissions.notifications.reminders.process'))
                ->addPermission('notifications.deliveries.view', tkey('permissions.notifications.deliveries.view'))
                ->addPermission('notifications.deliveries.manage', tkey('permissions.notifications.deliveries.manage'))
                ->addPermission('notifications.threads.view', tkey('permissions.notifications.threads.view'))
                ->addPermission('notifications.threads.manage', tkey('permissions.notifications.threads.manage'))
                ->addPermission('notifications.preferences.manage', tkey('permissions.notifications.preferences.manage'))
                ->addPermission('notifications.channels.manage', tkey('permissions.notifications.channels.manage'))
                ->addPermission('notifications.export', tkey('permissions.notifications.export')),

            ItemPermission::group(tkey('permissions.groups.system'))
                ->addPermission('platform.systems.roles', tkey('permissions.system.roles'))
                ->addPermission('platform.systems.users', tkey('permissions.system.users'))
                ->addPermission('security.users.view', tkey('permissions.security.users.view'))
                ->addPermission('security.users.create', tkey('permissions.security.users.create'))
                ->addPermission('security.users.update', tkey('permissions.security.users.update'))
                ->addPermission('security.users.block', tkey('permissions.security.users.block'))
                ->addPermission('security.users.unblock', tkey('permissions.security.users.unblock'))
                ->addPermission('security.users.archive', tkey('permissions.security.users.archive'))
                ->addPermission('security.users.change_status', tkey('permissions.security.users.change_status'))
                ->addPermission('security.users.override_status_transition', tkey('permissions.security.users.override_status_transition'))
                ->addPermission('security.users.force_password_change', tkey('permissions.security.users.force_password_change'))
                ->addPermission('security.users.update_profile', tkey('permissions.security.users.update_profile'))
                ->addPermission('security.users.view_security_summary', tkey('permissions.security.users.view_security_summary'))
                ->addPermission('security.permissions.manage', tkey('permissions.system.permission_registry'))
                ->addPermission('security.permissions.sync', tkey('permissions.system.permission_registry_sync'))
                ->addPermission('security.login_attempts.view', tkey('permissions.security.login_attempts.view'))
                ->addPermission('security.login_attempts.export', tkey('permissions.security.login_attempts.export'))
                ->addPermission('security.sessions.view', tkey('permissions.security.sessions.view'))
                ->addPermission('security.sessions.revoke', tkey('permissions.security.sessions.revoke'))
                ->addPermission('security.sessions.revoke_own', tkey('permissions.security.sessions.revoke_own'))
                ->addPermission('security.sessions.revoke_all', tkey('permissions.security.sessions.revoke_all'))
                ->addPermission('security.sessions.export', tkey('permissions.security.sessions.export'))
                ->addPermission('security.user_statuses.manage', tkey('permissions.system.user_statuses'))
                ->addPermission('security.staff_profiles.manage', tkey('permissions.system.staff_profiles'))
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
