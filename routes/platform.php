<?php

declare(strict_types=1);

use App\Orchid\Screens\LandingPage\LandingPageEditScreen;
use App\Orchid\Screens\PlatformScreen;
use App\Orchid\Screens\Role\RoleEditScreen;
use App\Orchid\Screens\Role\RoleListScreen;
use App\Orchid\Screens\School\BranchEditScreen;
use App\Orchid\Screens\School\BranchListScreen;
use App\Orchid\Screens\School\CampaignListScreen;
use App\Orchid\Screens\School\DocumentListScreen;
use App\Orchid\Screens\School\ExamListScreen;
use App\Orchid\Screens\School\FleetListScreen;
use App\Orchid\Screens\School\GroupEditScreen;
use App\Orchid\Screens\School\GroupListScreen;
use App\Orchid\Screens\School\InstructorListScreen;
use App\Orchid\Screens\School\LeadDictionaryEditScreen;
use App\Orchid\Screens\School\LeadDictionaryListScreen;
use App\Orchid\Screens\School\LeadEditScreen;
use App\Orchid\Screens\School\LeadListScreen;
use App\Orchid\Screens\School\LeadLostReasonListScreen;
use App\Orchid\Screens\School\LeadPipelineScreen;
use App\Orchid\Screens\School\LeadSourceListScreen;
use App\Orchid\Screens\School\LeadStatusListScreen;
use App\Orchid\Screens\School\LeadTagListScreen;
use App\Orchid\Screens\School\LeadTaskListScreen;
use App\Orchid\Screens\School\MessageTemplateEditScreen;
use App\Orchid\Screens\School\MessageTemplateListScreen;
use App\Orchid\Screens\School\PaymentListScreen;
use App\Orchid\Screens\School\PricingPackageEditScreen;
use App\Orchid\Screens\School\PricingPackageListScreen;
use App\Orchid\Screens\School\ProgramEditScreen;
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
use App\Orchid\Screens\Website\BranchEditScreen as WebsiteBranchEditScreen;
use App\Orchid\Screens\Website\BranchListScreen as WebsiteBranchListScreen;
use App\Orchid\Screens\Website\CourseCategoryEditScreen;
use App\Orchid\Screens\Website\CourseCategoryListScreen;
use App\Orchid\Screens\Website\CourseEditScreen;
use App\Orchid\Screens\Website\CourseListScreen;
use App\Orchid\Screens\Website\FaqEditScreen;
use App\Orchid\Screens\Website\FaqListScreen;
use App\Orchid\Screens\Website\PricingPackageEditScreen as WebsitePricingPackageEditScreen;
use App\Orchid\Screens\Website\PricingPackageListScreen as WebsitePricingPackageListScreen;
use App\Orchid\Screens\Website\SitePageEditScreen;
use App\Orchid\Screens\Website\SitePageListScreen;
use App\Orchid\Screens\Website\SiteSettingsScreen;
use App\Orchid\Screens\Website\TestimonialEditScreen;
use App\Orchid\Screens\Website\TestimonialListScreen;
use App\Orchid\Screens\Website\WebsiteGroupListScreen;
use App\Orchid\Screens\Website\WebsiteLeadListScreen;
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
        ->push(tkey('menu.content.home'), route('platform.content.home')));

// Website > Pages
Route::screen('website/pages/create', SitePageEditScreen::class)
    ->name('platform.website.pages.create')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.website.pages')
        ->push(tkey('website.admin.pages.create_title'), route('platform.website.pages.create')));

Route::screen('website/pages/{page}/edit', SitePageEditScreen::class)
    ->name('platform.website.pages.edit')
    ->breadcrumbs(fn (Trail $trail, $page) => $trail
        ->parent('platform.website.pages')
        ->push(tkey('website.admin.pages.edit_title'), route('platform.website.pages.edit', $page)));

Route::screen('website/pages', SitePageListScreen::class)
    ->name('platform.website.pages')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push(tkey('menu.website.pages'), route('platform.website.pages')));

// Website > Course Categories
Route::screen('website/course-categories/create', CourseCategoryEditScreen::class)
    ->name('platform.website.course-categories.create')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.website.course-categories')
        ->push(tkey('website.admin.course_categories.create_title'), route('platform.website.course-categories.create')));

Route::screen('website/course-categories/{category}/edit', CourseCategoryEditScreen::class)
    ->name('platform.website.course-categories.edit')
    ->breadcrumbs(fn (Trail $trail, $category) => $trail
        ->parent('platform.website.course-categories')
        ->push(tkey('website.admin.course_categories.edit_title'), route('platform.website.course-categories.edit', $category)));

Route::screen('website/course-categories', CourseCategoryListScreen::class)
    ->name('platform.website.course-categories')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push(tkey('menu.website.course_categories'), route('platform.website.course-categories')));

// Website > Courses
Route::screen('website/courses/create', CourseEditScreen::class)
    ->name('platform.website.courses.create')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.website.courses')
        ->push(tkey('website.admin.courses.create_title'), route('platform.website.courses.create')));

Route::screen('website/courses/{program}/edit', CourseEditScreen::class)
    ->name('platform.website.courses.edit')
    ->breadcrumbs(fn (Trail $trail, $program) => $trail
        ->parent('platform.website.courses')
        ->push(tkey('website.admin.courses.edit_title'), route('platform.website.courses.edit', $program)));

Route::screen('website/courses', CourseListScreen::class)
    ->name('platform.website.courses')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push(tkey('menu.website.courses'), route('platform.website.courses')));

// Website > Pricing
Route::screen('website/pricing/create', WebsitePricingPackageEditScreen::class)
    ->name('platform.website.pricing.create')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.website.pricing')
        ->push(tkey('website.admin.pricing.create_title'), route('platform.website.pricing.create')));

Route::screen('website/pricing/{pricingPackage}/edit', WebsitePricingPackageEditScreen::class)
    ->name('platform.website.pricing.edit')
    ->breadcrumbs(fn (Trail $trail, $pricingPackage) => $trail
        ->parent('platform.website.pricing')
        ->push(tkey('website.admin.pricing.edit_title'), route('platform.website.pricing.edit', $pricingPackage)));

Route::screen('website/pricing', WebsitePricingPackageListScreen::class)
    ->name('platform.website.pricing')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push(tkey('menu.website.pricing'), route('platform.website.pricing')));

// Website > Branches
Route::screen('website/branches/create', WebsiteBranchEditScreen::class)
    ->name('platform.website.branches.create')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.website.branches')
        ->push(tkey('website.admin.branches.create_title'), route('platform.website.branches.create')));

Route::screen('website/branches/{branch}/edit', WebsiteBranchEditScreen::class)
    ->name('platform.website.branches.edit')
    ->breadcrumbs(fn (Trail $trail, $branch) => $trail
        ->parent('platform.website.branches')
        ->push(tkey('website.admin.branches.edit_title'), route('platform.website.branches.edit', $branch)));

Route::screen('website/branches', WebsiteBranchListScreen::class)
    ->name('platform.website.branches')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push(tkey('menu.website.branches'), route('platform.website.branches')));

// Website > Groups
Route::screen('website/groups/create', GroupEditScreen::class)
    ->name('platform.website.groups.create')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.website.groups')
        ->push(tkey('website.admin.groups.create_title'), route('platform.website.groups.create')));

Route::screen('website/groups/{group}/edit', GroupEditScreen::class)
    ->name('platform.website.groups.edit')
    ->breadcrumbs(fn (Trail $trail, $group) => $trail
        ->parent('platform.website.groups')
        ->push(tkey('website.admin.groups.edit_title'), route('platform.website.groups.edit', $group)));

Route::screen('website/groups', WebsiteGroupListScreen::class)
    ->name('platform.website.groups')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push(tkey('menu.website.groups'), route('platform.website.groups')));

// Website > FAQ
Route::screen('website/faq/create', FaqEditScreen::class)
    ->name('platform.website.faq.create')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.website.faq')
        ->push(tkey('website.admin.faq.create_title'), route('platform.website.faq.create')));

Route::screen('website/faq/{faq}/edit', FaqEditScreen::class)
    ->name('platform.website.faq.edit')
    ->breadcrumbs(fn (Trail $trail, $faq) => $trail
        ->parent('platform.website.faq')
        ->push(tkey('website.admin.faq.edit_title'), route('platform.website.faq.edit', $faq)));

Route::screen('website/faq', FaqListScreen::class)
    ->name('platform.website.faq')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push(tkey('menu.website.faq'), route('platform.website.faq')));

// Website > Testimonials
Route::screen('website/testimonials/create', TestimonialEditScreen::class)
    ->name('platform.website.testimonials.create')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.website.testimonials')
        ->push(tkey('website.admin.testimonials.create_title'), route('platform.website.testimonials.create')));

Route::screen('website/testimonials/{testimonial}/edit', TestimonialEditScreen::class)
    ->name('platform.website.testimonials.edit')
    ->breadcrumbs(fn (Trail $trail, $testimonial) => $trail
        ->parent('platform.website.testimonials')
        ->push(tkey('website.admin.testimonials.edit_title'), route('platform.website.testimonials.edit', $testimonial)));

Route::screen('website/testimonials', TestimonialListScreen::class)
    ->name('platform.website.testimonials')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push(tkey('menu.website.testimonials'), route('platform.website.testimonials')));

// Website > Leads
Route::screen('website/leads', WebsiteLeadListScreen::class)
    ->name('platform.website.leads')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push(tkey('menu.website.leads'), route('platform.website.leads')));

// Website > Settings
Route::screen('website/settings', SiteSettingsScreen::class)
    ->name('platform.website.settings')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push(tkey('menu.website.settings'), route('platform.website.settings')));

// Operations > Branches
Route::screen('operations/branches', BranchListScreen::class)
    ->name('platform.operations.branches')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push(tkey('menu.operations.branches'), route('platform.operations.branches')));

// Operations > Instructors
Route::screen('operations/instructors', InstructorListScreen::class)
    ->name('platform.operations.instructors')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push(tkey('menu.operations.instructors'), route('platform.operations.instructors')));

// Operations > Groups
Route::screen('operations/groups', GroupListScreen::class)
    ->name('platform.operations.groups')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push(tkey('menu.operations.groups'), route('platform.operations.groups')));

// Operations > Student CRM
Route::screen('crm/students', StudentListScreen::class)
    ->name('platform.crm.students')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push(tkey('menu.crm.students'), route('platform.crm.students')));

// Learning > Programs
Route::screen('lms/programs', ProgramListScreen::class)
    ->name('platform.lms.programs')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push(tkey('menu.lms.programs'), route('platform.lms.programs')));

// Operations > Schedule
Route::screen('schedule/lessons', ScheduleListScreen::class)
    ->name('platform.schedule.lessons')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push(tkey('menu.schedule.lessons'), route('platform.schedule.lessons')));

// Operations > Fleet
Route::screen('fleet/vehicles', FleetListScreen::class)
    ->name('platform.fleet.vehicles')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push(tkey('menu.fleet.vehicles'), route('platform.fleet.vehicles')));

// Operations > Exams
Route::screen('exams', ExamListScreen::class)
    ->name('platform.exams')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push(tkey('menu.exams'), route('platform.exams')));

// Finance > Payments
Route::screen('finance/payments', PaymentListScreen::class)
    ->name('platform.finance.payments')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push(tkey('menu.finance.payments'), route('platform.finance.payments')));

// Operations > Documents
Route::screen('documents', DocumentListScreen::class)
    ->name('platform.documents')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push(tkey('menu.documents'), route('platform.documents')));

// Marketing > Campaigns
Route::screen('marketing/campaigns', CampaignListScreen::class)
    ->name('platform.marketing.campaigns')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push(tkey('menu.marketing.campaigns'), route('platform.marketing.campaigns')));

// Marketing > Sales Pipeline
Route::screen('marketing/pipeline', LeadPipelineScreen::class)
    ->name('platform.marketing.pipeline')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push(tkey('menu.crm.pipeline'), route('platform.marketing.pipeline')));

// CRM > Pipeline
Route::screen('crm/pipeline', LeadPipelineScreen::class)
    ->name('platform.crm.pipeline')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push(tkey('menu.crm.pipeline'), route('platform.crm.pipeline')));

// Marketing > Leads
Route::screen('marketing/leads/create', LeadEditScreen::class)
    ->name('platform.marketing.leads.create')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.marketing.leads')
        ->push(tkey('crm.leads.create_title'), route('platform.marketing.leads.create')));

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

// CRM > Leads
Route::screen('crm/leads/create', LeadEditScreen::class)
    ->name('platform.crm.leads.create')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.crm.leads')
        ->push(tkey('crm.leads.create_title'), route('platform.crm.leads.create')));

Route::screen('crm/leads/{lead}/edit', LeadEditScreen::class)
    ->name('platform.crm.leads.edit')
    ->breadcrumbs(fn (Trail $trail, $lead) => $trail
        ->parent('platform.crm.leads')
        ->push($lead->fullName(), route('platform.crm.leads.edit', $lead)));

Route::screen('crm/leads', LeadListScreen::class)
    ->name('platform.crm.leads')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push(tkey('menu.crm.leads'), route('platform.crm.leads')));

// CRM > Tasks
Route::screen('crm/tasks', LeadTaskListScreen::class)
    ->name('platform.crm.tasks')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push(tkey('menu.crm.tasks'), route('platform.crm.tasks')));

// CRM > Dictionary shortcuts
Route::screen('crm/statuses', LeadStatusListScreen::class)
    ->name('platform.crm.statuses')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push(tkey('menu.crm.statuses'), route('platform.crm.statuses')));

Route::screen('crm/sources', LeadSourceListScreen::class)
    ->name('platform.crm.sources')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push(tkey('menu.crm.sources'), route('platform.crm.sources')));

Route::screen('crm/lost-reasons', LeadLostReasonListScreen::class)
    ->name('platform.crm.lost-reasons')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push(tkey('menu.crm.lost_reasons'), route('platform.crm.lost-reasons')));

Route::screen('crm/tags', LeadTagListScreen::class)
    ->name('platform.crm.tags')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push(tkey('menu.crm.tags'), route('platform.crm.tags')));

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
