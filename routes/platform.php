<?php

declare(strict_types=1);

use App\Orchid\Screens\LandingPage\LandingPageEditScreen;
use App\Orchid\Screens\PlatformScreen;
use App\Orchid\Screens\Role\RoleEditScreen;
use App\Orchid\Screens\Role\RoleListScreen;
use App\Orchid\Screens\School\BranchListScreen;
use App\Orchid\Screens\School\CampaignListScreen;
use App\Orchid\Screens\School\DocumentListScreen;
use App\Orchid\Screens\School\ExamListScreen;
use App\Orchid\Screens\School\FleetListScreen;
use App\Orchid\Screens\School\GroupListScreen;
use App\Orchid\Screens\School\InstructorListScreen;
use App\Orchid\Screens\School\LeadDictionaryEditScreen;
use App\Orchid\Screens\School\LeadDictionaryListScreen;
use App\Orchid\Screens\School\LeadEditScreen;
use App\Orchid\Screens\School\LeadListScreen;
use App\Orchid\Screens\School\LeadPipelineScreen;
use App\Orchid\Screens\School\MessageTemplateEditScreen;
use App\Orchid\Screens\School\MessageTemplateListScreen;
use App\Orchid\Screens\School\PaymentListScreen;
use App\Orchid\Screens\School\ProgramListScreen;
use App\Orchid\Screens\School\ScheduleListScreen;
use App\Orchid\Screens\School\StudentListScreen;
use App\Orchid\Screens\System\LanguageEditScreen;
use App\Orchid\Screens\System\LanguageListScreen;
use App\Orchid\Screens\System\TranslationEditScreen;
use App\Orchid\Screens\System\TranslationListScreen;
use App\Orchid\Screens\User\UserEditScreen;
use App\Orchid\Screens\User\UserListScreen;
use App\Orchid\Screens\User\UserProfileScreen;
use App\Support\Crm\LeadDictionaryRegistry;
use Illuminate\Support\Facades\Route;
use Tabuna\Breadcrumbs\Trail;

/*
|--------------------------------------------------------------------------
| Dashboard Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the need "dashboard" middleware group. Now create something great!
|
*/

// Main
Route::screen('/main', PlatformScreen::class)
    ->name('platform.main');

// Content > Homepage
Route::screen('content/home', LandingPageEditScreen::class)
    ->name('platform.content.home')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push('Homepage', route('platform.content.home')));

// Operations > Branches
Route::screen('operations/branches', BranchListScreen::class)
    ->name('platform.operations.branches')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push('Branches', route('platform.operations.branches')));

// Operations > Instructors
Route::screen('operations/instructors', InstructorListScreen::class)
    ->name('platform.operations.instructors')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push('Instructors', route('platform.operations.instructors')));

// Operations > Groups
Route::screen('operations/groups', GroupListScreen::class)
    ->name('platform.operations.groups')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push('Training groups', route('platform.operations.groups')));

// Operations > Student CRM
Route::screen('crm/students', StudentListScreen::class)
    ->name('platform.crm.students')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push('Student CRM', route('platform.crm.students')));

// Learning > Programs
Route::screen('lms/programs', ProgramListScreen::class)
    ->name('platform.lms.programs')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push('LMS Programs', route('platform.lms.programs')));

// Operations > Schedule
Route::screen('schedule/lessons', ScheduleListScreen::class)
    ->name('platform.schedule.lessons')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push('Schedule', route('platform.schedule.lessons')));

// Operations > Fleet
Route::screen('fleet/vehicles', FleetListScreen::class)
    ->name('platform.fleet.vehicles')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push('Fleet', route('platform.fleet.vehicles')));

// Operations > Exams
Route::screen('exams', ExamListScreen::class)
    ->name('platform.exams')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push('Exams', route('platform.exams')));

// Finance > Payments
Route::screen('finance/payments', PaymentListScreen::class)
    ->name('platform.finance.payments')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push('Payments', route('platform.finance.payments')));

// Operations > Documents
Route::screen('documents', DocumentListScreen::class)
    ->name('platform.documents')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push('Documents', route('platform.documents')));

// Marketing > Campaigns
Route::screen('marketing/campaigns', CampaignListScreen::class)
    ->name('platform.marketing.campaigns')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push('Campaigns', route('platform.marketing.campaigns')));

// Marketing > Sales Pipeline
Route::screen('marketing/pipeline', LeadPipelineScreen::class)
    ->name('platform.marketing.pipeline')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push(tkey('menu.crm.pipeline'), route('platform.marketing.pipeline')));

// Marketing > Leads
Route::screen('marketing/leads', LeadListScreen::class)
    ->name('platform.marketing.leads')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push(tkey('menu.crm.leads'), route('platform.marketing.leads')));

// Marketing > Leads > CRM card
Route::screen('marketing/leads/{lead}/edit', LeadEditScreen::class)
    ->name('platform.marketing.leads.edit')
    ->breadcrumbs(fn (Trail $trail, $lead) => $trail
        ->parent('platform.marketing.leads')
        ->push($lead->fullName(), route('platform.marketing.leads.edit', $lead)));

// CRM > Dictionaries
Route::screen('crm/dictionaries/{dictionary}/create', LeadDictionaryEditScreen::class)
    ->name('platform.crm.dictionaries.create')
    ->breadcrumbs(fn (Trail $trail, string $dictionary) => $trail
        ->parent('platform.crm.dictionaries', $dictionary)
        ->push(tkey('crm.dictionaries.create_title'), route('platform.crm.dictionaries.create', $dictionary)));

Route::screen('crm/dictionaries/{dictionary}/{record}/edit', LeadDictionaryEditScreen::class)
    ->name('platform.crm.dictionaries.edit')
    ->breadcrumbs(fn (Trail $trail, string $dictionary, string $record) => $trail
        ->parent('platform.crm.dictionaries', $dictionary)
        ->push(tkey('crm.dictionaries.edit_title'), route('platform.crm.dictionaries.edit', [$dictionary, $record])));

Route::screen('crm/dictionaries/{dictionary}', LeadDictionaryListScreen::class)
    ->name('platform.crm.dictionaries')
    ->breadcrumbs(fn (Trail $trail, string $dictionary) => $trail
        ->parent('platform.index')
        ->push(tkey(LeadDictionaryRegistry::definition($dictionary)['title_key']), route('platform.crm.dictionaries', $dictionary)));

// Marketing > Message Templates
Route::screen('marketing/message-templates/create', MessageTemplateEditScreen::class)
    ->name('platform.marketing.templates.create')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.marketing.templates')
        ->push(tkey('crm.message_templates.create_title'), route('platform.marketing.templates.create')));

Route::screen('marketing/message-templates/{messageTemplate}/edit', MessageTemplateEditScreen::class)
    ->name('platform.marketing.templates.edit')
    ->breadcrumbs(fn (Trail $trail, $messageTemplate) => $trail
        ->parent('platform.marketing.templates')
        ->push($messageTemplate->name, route('platform.marketing.templates.edit', $messageTemplate)));

Route::screen('marketing/message-templates', MessageTemplateListScreen::class)
    ->name('platform.marketing.templates')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push(tkey('crm.message_templates.title'), route('platform.marketing.templates')));

// System > Languages
Route::screen('system/languages/create', LanguageEditScreen::class)
    ->name('platform.system.languages.create')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.system.languages')
        ->push(tkey('languages.create_title'), route('platform.system.languages.create')));

Route::screen('system/languages/{language}/edit', LanguageEditScreen::class)
    ->name('platform.system.languages.edit')
    ->breadcrumbs(fn (Trail $trail, $language) => $trail
        ->parent('platform.system.languages')
        ->push($language->name, route('platform.system.languages.edit', $language)));

Route::screen('system/languages', LanguageListScreen::class)
    ->name('platform.system.languages')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push(tkey('menu.settings.languages'), route('platform.system.languages')));

// System > Translations
Route::screen('system/translations/create', TranslationEditScreen::class)
    ->name('platform.system.translations.create')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.system.translations')
        ->push(tkey('translations.create_title'), route('platform.system.translations.create')));

Route::screen('system/translations/{translationString}/edit', TranslationEditScreen::class)
    ->name('platform.system.translations.edit')
    ->breadcrumbs(fn (Trail $trail, $translationString) => $trail
        ->parent('platform.system.translations')
        ->push($translationString->key, route('platform.system.translations.edit', $translationString)));

Route::screen('system/translations', TranslationListScreen::class)
    ->name('platform.system.translations')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push(tkey('menu.settings.translations'), route('platform.system.translations')));

// Platform > Profile
Route::screen('profile', UserProfileScreen::class)
    ->name('platform.profile')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push(__('Profile'), route('platform.profile')));

// Platform > System > Users > User
Route::screen('users/{user}/edit', UserEditScreen::class)
    ->name('platform.systems.users.edit')
    ->breadcrumbs(fn (Trail $trail, $user) => $trail
        ->parent('platform.systems.users')
        ->push($user->name, route('platform.systems.users.edit', $user)));

// Platform > System > Users > Create
Route::screen('users/create', UserEditScreen::class)
    ->name('platform.systems.users.create')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.systems.users')
        ->push(__('Create'), route('platform.systems.users.create')));

// Platform > System > Users
Route::screen('users', UserListScreen::class)
    ->name('platform.systems.users')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push(__('Users'), route('platform.systems.users')));

// Platform > System > Roles > Role
Route::screen('roles/{role}/edit', RoleEditScreen::class)
    ->name('platform.systems.roles.edit')
    ->breadcrumbs(fn (Trail $trail, $role) => $trail
        ->parent('platform.systems.roles')
        ->push($role->name, route('platform.systems.roles.edit', $role)));

// Platform > System > Roles > Create
Route::screen('roles/create', RoleEditScreen::class)
    ->name('platform.systems.roles.create')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.systems.roles')
        ->push(__('Create'), route('platform.systems.roles.create')));

// Platform > System > Roles
Route::screen('roles', RoleListScreen::class)
    ->name('platform.systems.roles')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push(__('Roles'), route('platform.systems.roles')));

// Route::screen('idea', Idea::class, 'platform.screens.idea');
