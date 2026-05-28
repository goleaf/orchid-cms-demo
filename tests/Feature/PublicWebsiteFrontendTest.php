<?php

namespace Tests\Feature;

use App\Enums\GroupStatus;
use App\Enums\LeadTaskPriority;
use App\Enums\LeadTaskStatus;
use App\Models\Branch;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\MarketingLead;
use App\Models\PricingPackage;
use App\Models\TrainingGroup;
use App\Models\TrainingProgram;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PublicWebsiteFrontendTest extends TestCase
{
    use RefreshDatabase;

    public function test_required_public_website_pages_load(): void
    {
        $this->seed();

        $program = TrainingProgram::query()
            ->where('slug', 'category-b-manual')
            ->firstOrFail();
        $branch = Branch::query()
            ->where('slug', 'vilnius-main')
            ->firstOrFail();

        collect([
            route('website.home') => tkey('website.home.programs.title', [], 'ru'),
            route('website.courses.index') => tkey('website.courses.title', [], 'ru'),
            route('website.courses.show', $program) => $program->displayTitle('ru'),
            route('website.pricing') => tkey('website.prices.title', [], 'ru'),
            route('website.branches.index') => tkey('website.branches.title', [], 'ru'),
            route('website.branches.show', ['branch' => $branch->slug]) => $branch->displayName('ru'),
            route('website.contacts') => tkey('website.contacts.title', [], 'ru'),
            route('website.thank_you') => tkey('website.forms.messages.thank_you_title', [], 'ru'),
        ])->each(function (string $needle, string $url): void {
            $this->get($url)
                ->assertOk()
                ->assertSee($needle);
        });
    }

    public function test_application_form_creates_lead_with_utm_and_selected_context(): void
    {
        Notification::fake();
        $this->seed();

        $program = TrainingProgram::query()
            ->where('slug', 'category-b-manual')
            ->firstOrFail();
        $branch = Branch::query()
            ->where('slug', 'vilnius-main')
            ->firstOrFail();
        $group = TrainingGroup::query()
            ->where('code', 'B-VNO-001')
            ->firstOrFail();

        $this->get('/courses?utm_source=google&utm_medium=cpc&utm_campaign=frontend');

        $this->post(route('website.leads.store'), [
            'course_id' => $program->id,
            'branch_id' => $branch->id,
            'training_group_id' => $group->id,
            'full_name' => 'Frontend Lead',
            'phone' => '+370 600 12345',
            'email' => 'frontend.lead@example.com',
            'preferred_format' => 'mixed',
            'preferred_language' => 'en',
            'preferred_time' => 'Evenings',
            'preferred_messenger' => 'Telegram',
            'comment' => 'Interested in this group.',
            'consent_accepted' => '1',
            'form_name' => 'frontend_test_application',
        ])
            ->assertRedirect(route('website.thank_you'))
            ->assertSessionHasNoErrors();

        $lead = MarketingLead::query()
            ->where('email', 'frontend.lead@example.com')
            ->firstOrFail();

        $this->assertSame('website', $lead->source);
        $this->assertSame('new', $lead->status->value);
        $this->assertSame('frontend_test_application', $lead->form_name);
        $this->assertTrue($lead->consent_accepted);
        $this->assertNotNull($lead->consent_accepted_at);
        $this->assertSame('google', $lead->utm_source);
        $this->assertSame('frontend', $lead->utm_campaign);
        $this->assertSame($program->id, $lead->training_program_id);
        $this->assertSame($branch->id, $lead->branch_id);
        $this->assertSame($group->id, $lead->training_group_id);
        $this->assertSame('Interested in this group.', $lead->message);
        $this->assertStringContainsString('utm_source=google', (string) $lead->landing_page);
        $this->assertTrue($lead->activities()->where('type', 'created_from_website')->exists());

        $task = $lead->tasks()->firstOrFail();
        $this->assertSame(tkey('crm.tasks.defaults.contact_new_website_lead'), $task->title);
        $this->assertSame(LeadTaskPriority::High, $task->priority);
        $this->assertSame(LeadTaskStatus::Open, $task->status);
        $this->assertNotNull($task->due_at);
    }

    public function test_application_form_marks_possible_duplicate_without_blocking_creation(): void
    {
        Notification::fake();
        $this->seed();

        $program = TrainingProgram::query()
            ->where('slug', 'category-b-manual')
            ->firstOrFail();
        $branch = Branch::query()
            ->where('slug', 'vilnius-main')
            ->firstOrFail();
        $original = MarketingLead::factory()->create([
            'first_name' => 'Original',
            'last_name' => 'Application',
            'phone' => '+999 111 222333',
            'email' => 'original.application@example.com',
        ]);

        $this->post(route('website.leads.store'), [
            'course_id' => $program->id,
            'branch_id' => $branch->id,
            'full_name' => 'Duplicate Application',
            'phone' => '999111222333',
            'email' => 'duplicate.application@example.com',
            'preferred_format' => 'mixed',
            'preferred_language' => 'en',
            'consent_accepted' => '1',
        ])
            ->assertRedirect(route('website.thank_you'))
            ->assertSessionHasNoErrors();

        $duplicate = MarketingLead::query()
            ->where('email', 'duplicate.application@example.com')
            ->firstOrFail();

        $this->assertSame($original->id, $duplicate->duplicate_of_id);
        $this->assertSame('new', $duplicate->status->value);
        $this->assertTrue($duplicate->activities()->where('type', 'created_from_website')->exists());
        $this->assertDatabaseHas('marketing_lead_activities', [
            'marketing_lead_id' => $duplicate->id,
            'type' => 'marked_duplicate',
            'new_value' => (string) $original->id,
        ]);
    }

    public function test_callback_and_contact_forms_create_leads(): void
    {
        Notification::fake();
        $this->seed();

        $this->post(route('website.callback.store'), [
            'full_name' => 'Callback Lead',
            'phone' => '+370 600 22222',
            'callback_time' => 'Tomorrow morning',
            'comment' => 'Call me back.',
            'consent_accepted' => '1',
        ])
            ->assertRedirect(route('website.thank_you'))
            ->assertSessionHasNoErrors();

        $this->post(route('website.contact.store'), [
            'full_name' => 'Contact Lead',
            'phone' => '+370 600 33333',
            'email' => 'contact.lead@example.com',
            'comment' => 'I have a question.',
            'consent_accepted' => '1',
        ])
            ->assertRedirect(route('website.thank_you'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('marketing_leads', [
            'full_name' => 'Callback Lead',
            'source' => 'callback',
            'form_name' => 'callback',
            'preferred_time' => 'Tomorrow morning',
            'message' => 'Call me back.',
        ]);
        $this->assertDatabaseHas('marketing_leads', [
            'full_name' => 'Contact Lead',
            'source' => 'contact_form',
            'form_name' => 'contact',
            'message' => 'I have a question.',
        ]);

        $callbackLead = MarketingLead::query()
            ->where('full_name', 'Callback Lead')
            ->firstOrFail();
        $contactLead = MarketingLead::query()
            ->where('full_name', 'Contact Lead')
            ->firstOrFail();

        foreach ([$callbackLead, $contactLead] as $lead) {
            $this->assertSame('new', $lead->status->value);
            $this->assertTrue($lead->consent_accepted);
            $this->assertTrue($lead->activities()->where('type', 'created_from_website')->exists());

            $task = $lead->tasks()->firstOrFail();
            $this->assertSame(tkey('crm.tasks.defaults.contact_new_website_lead'), $task->title);
            $this->assertSame(LeadTaskPriority::High, $task->priority);
            $this->assertSame(LeadTaskStatus::Open, $task->status);
        }
    }

    public function test_public_frontend_hides_hidden_catalog_and_full_groups(): void
    {
        $this->seed();

        $visibleProgram = TrainingProgram::query()
            ->where('slug', 'category-b-manual')
            ->firstOrFail();
        $visibleBranch = Branch::query()
            ->where('slug', 'vilnius-main')
            ->firstOrFail();

        Course::factory()->create([
            'title' => 'Hidden Frontend Course',
            'title_translations' => ['ru' => 'Hidden Frontend Course'],
            'is_active' => true,
            'is_visible_on_site' => false,
        ]);
        Branch::factory()->create([
            'name' => 'Hidden Frontend Branch',
            'name_translations' => ['ru' => 'Hidden Frontend Branch'],
            'is_active' => true,
            'is_visible_on_site' => false,
        ]);
        TrainingGroup::factory()->create([
            'training_program_id' => $visibleProgram->id,
            'branch_id' => $visibleBranch->id,
            'name' => 'Full Frontend Group',
            'name_translations' => ['ru' => 'Full Frontend Group'],
            'status' => GroupStatus::Recruiting,
            'is_visible_on_site' => true,
            'capacity' => 8,
            'places_taken' => 8,
        ]);

        $this->get(route('website.courses.index'))
            ->assertOk()
            ->assertDontSee('Hidden Frontend Course')
            ->assertDontSee('Full Frontend Group');

        $this->get(route('website.branches.index'))
            ->assertOk()
            ->assertDontSee('Hidden Frontend Branch')
            ->assertDontSee('Full Frontend Group');
    }

    public function test_homepage_filters_courses_by_country_city_and_category(): void
    {
        $this->seed();

        $matchingCategory = CourseCategory::factory()->create([
            'code' => 'filter_match_category',
            'slug' => 'filter-match-category',
            'name_translations' => ['ru' => 'Фильтр категория', 'en' => 'Filter category'],
            'is_active' => true,
            'is_visible_on_site' => true,
        ]);
        $otherCategory = CourseCategory::factory()->create([
            'code' => 'filter_other_category',
            'slug' => 'filter-other-category',
            'name_translations' => ['ru' => 'Другая фильтр категория', 'en' => 'Other filter category'],
            'is_active' => true,
            'is_visible_on_site' => true,
        ]);
        $matchingBranch = Branch::factory()->create([
            'slug' => 'filter-vilnius-branch',
            'country' => 'Filterland',
            'country_translations' => ['ru' => 'Фильтрландия', 'en' => 'Filterland'],
            'city' => 'Filter City',
            'city_translations' => ['ru' => 'Фильтр Сити', 'en' => 'Filter City'],
            'is_active' => true,
            'is_visible_on_site' => true,
        ]);
        $otherBranch = Branch::factory()->create([
            'slug' => 'filter-riga-branch',
            'country' => 'Otherland',
            'country_translations' => ['ru' => 'Другая страна', 'en' => 'Otherland'],
            'city' => 'Other City',
            'city_translations' => ['ru' => 'Другой город', 'en' => 'Other City'],
            'is_active' => true,
            'is_visible_on_site' => true,
        ]);
        $matchingProgram = TrainingProgram::factory()->create([
            'course_category_id' => $matchingCategory->id,
            'title' => 'Unique Filter Matching Course',
            'title_translations' => ['ru' => 'Уникальный подходящий курс', 'en' => 'Unique Filter Matching Course'],
            'name_translations' => ['ru' => 'Уникальный подходящий курс', 'en' => 'Unique Filter Matching Course'],
            'slug' => 'unique-filter-matching-course',
            'is_active' => true,
            'is_visible_on_site' => true,
        ]);
        $otherProgram = TrainingProgram::factory()->create([
            'course_category_id' => $otherCategory->id,
            'title' => 'Unique Filter Other Course',
            'title_translations' => ['ru' => 'Уникальный другой курс', 'en' => 'Unique Filter Other Course'],
            'name_translations' => ['ru' => 'Уникальный другой курс', 'en' => 'Unique Filter Other Course'],
            'slug' => 'unique-filter-other-course',
            'is_active' => true,
            'is_visible_on_site' => true,
        ]);

        TrainingGroup::factory()->publicVisible()->emptyCapacity()->create([
            'branch_id' => $matchingBranch->id,
            'training_program_id' => $matchingProgram->id,
            'course_category_id' => $matchingCategory->id,
            'code' => 'FILTER-MATCH-001',
        ]);
        TrainingGroup::factory()->publicVisible()->emptyCapacity()->create([
            'branch_id' => $otherBranch->id,
            'training_program_id' => $otherProgram->id,
            'course_category_id' => $otherCategory->id,
            'code' => 'FILTER-OTHER-001',
        ]);

        $contextQuery = http_build_query([
            'country' => 'Filterland',
            'city' => 'Filter City',
        ]);

        $this->get(route('website.home', [
            'country' => 'Filterland',
            'city' => 'Filter City',
            'category' => 'filter-match-category',
        ]))
            ->assertOk()
            ->assertSee('Уникальный подходящий курс')
            ->assertDontSee('Уникальный другой курс')
            ->assertSee(route('website.courses.show', $matchingProgram).'?'.$contextQuery);
    }

    public function test_location_filter_options_follow_locale_and_country_city_relationship(): void
    {
        $this->seed();

        Branch::factory()->create([
            'slug' => 'locale-vilnius-branch',
            'name_translations' => ['ru' => 'Локальный Вильнюс', 'en' => 'Locale Vilnius', 'lt' => 'Lokalus Vilnius', 'pl' => 'Lokalne Wilno'],
            'country' => 'Lithuania',
            'country_translations' => ['ru' => 'Литва', 'en' => 'Lithuania', 'lt' => 'Lietuva', 'pl' => 'Litwa'],
            'city' => 'Vilnius',
            'city_translations' => ['ru' => 'Вильнюс', 'en' => 'Vilnius', 'lt' => 'Vilniaus miestas', 'pl' => 'Wilno'],
            'is_active' => true,
            'is_visible_on_site' => true,
        ]);
        Branch::factory()->create([
            'slug' => 'locale-riga-branch',
            'name_translations' => ['ru' => 'Локальная Рига', 'en' => 'Locale Riga', 'lt' => 'Lokalus Ryga', 'pl' => 'Lokalna Ryga'],
            'country' => 'Latvia',
            'country_translations' => ['ru' => 'Латвия', 'en' => 'Latvia', 'lt' => 'Latvija', 'pl' => 'Lotwa'],
            'city' => 'Riga',
            'city_translations' => ['ru' => 'Рига', 'en' => 'Riga', 'lt' => 'Ryga', 'pl' => 'Ryga'],
            'is_active' => true,
            'is_visible_on_site' => true,
        ]);

        $this
            ->withSession(['locale' => 'pl'])
            ->get(route('website.home', ['country' => 'Lithuania']))
            ->assertOk()
            ->assertSee('Litwa')
            ->assertSee('Wilno')
            ->assertDontSee('Ryga');
    }

    public function test_branches_page_filters_schools_by_country_and_city(): void
    {
        $this->seed();

        $matchingBranch = Branch::factory()->create([
            'slug' => 'branch-filter-school',
            'name' => 'Branch Filter School',
            'name_translations' => ['ru' => 'Фильтр школа филиал', 'en' => 'Branch Filter School'],
            'country' => 'Filterland',
            'country_translations' => ['ru' => 'Фильтрландия', 'en' => 'Filterland'],
            'city' => 'Filter City',
            'city_translations' => ['ru' => 'Фильтр Сити', 'en' => 'Filter City'],
            'is_active' => true,
            'is_visible_on_site' => true,
        ]);
        $otherBranch = Branch::factory()->create([
            'slug' => 'branch-other-school',
            'name' => 'Branch Other School',
            'name_translations' => ['ru' => 'Другая школа филиал', 'en' => 'Branch Other School'],
            'country' => 'Otherland',
            'country_translations' => ['ru' => 'Другая страна', 'en' => 'Otherland'],
            'city' => 'Other City',
            'city_translations' => ['ru' => 'Другой город', 'en' => 'Other City'],
            'is_active' => true,
            'is_visible_on_site' => true,
        ]);
        $program = TrainingProgram::factory()->create([
            'title_translations' => ['ru' => 'Фильтр курс филиала', 'en' => 'Branch filter course'],
            'name_translations' => ['ru' => 'Фильтр курс филиала', 'en' => 'Branch filter course'],
            'is_active' => true,
            'is_visible_on_site' => true,
        ]);

        TrainingGroup::factory()->publicVisible()->emptyCapacity()->create([
            'branch_id' => $matchingBranch->id,
            'training_program_id' => $program->id,
            'code' => 'BRANCH-FILTER-001',
        ]);
        TrainingGroup::factory()->publicVisible()->emptyCapacity()->create([
            'branch_id' => $otherBranch->id,
            'training_program_id' => $program->id,
            'code' => 'BRANCH-OTHER-001',
        ]);

        $this->get(route('website.branches.index', [
            'country' => 'Filterland',
            'city' => 'Filter City',
        ]))
            ->assertOk()
            ->assertSee('Фильтр школа филиал')
            ->assertSee('Фильтр Сити')
            ->assertDontSee('Другая школа филиал')
            ->assertDontSee('Другой город')
            ->assertSee(tkey('website.filters.active', [], 'ru'));
    }

    public function test_courses_page_filters_by_country_city_and_category(): void
    {
        $this->seed();

        $matchingCategory = CourseCategory::factory()->create([
            'code' => 'course_filter_category',
            'slug' => 'course-filter-category',
            'name_translations' => ['ru' => 'Категория фильтра курсов', 'en' => 'Course filter category'],
            'is_active' => true,
            'is_visible_on_site' => true,
        ]);
        $otherCategory = CourseCategory::factory()->create([
            'code' => 'course_other_category',
            'slug' => 'course-other-category',
            'name_translations' => ['ru' => 'Другая категория курсов', 'en' => 'Other course category'],
            'is_active' => true,
            'is_visible_on_site' => true,
        ]);
        $matchingBranch = Branch::factory()->create([
            'country' => 'Course Filterland',
            'country_translations' => ['ru' => 'Страна фильтра курсов', 'en' => 'Course Filterland'],
            'city' => 'Course City',
            'city_translations' => ['ru' => 'Город фильтра курсов', 'en' => 'Course City'],
            'is_active' => true,
            'is_visible_on_site' => true,
        ]);
        $otherBranch = Branch::factory()->create([
            'country' => 'Course Otherland',
            'country_translations' => ['ru' => 'Другая страна курсов', 'en' => 'Course Otherland'],
            'city' => 'Course Other City',
            'city_translations' => ['ru' => 'Другой город курсов', 'en' => 'Course Other City'],
            'is_active' => true,
            'is_visible_on_site' => true,
        ]);
        $matchingProgram = TrainingProgram::factory()->create([
            'course_category_id' => $matchingCategory->id,
            'title' => 'Unique Course Filter Match',
            'title_translations' => ['ru' => 'Уникальный курс фильтра курсов', 'en' => 'Unique Course Filter Match'],
            'name_translations' => ['ru' => 'Уникальный курс фильтра курсов', 'en' => 'Unique Course Filter Match'],
            'slug' => 'unique-course-filter-match',
            'is_active' => true,
            'is_visible_on_site' => true,
        ]);
        $otherProgram = TrainingProgram::factory()->create([
            'course_category_id' => $otherCategory->id,
            'title' => 'Unique Course Filter Other',
            'title_translations' => ['ru' => 'Уникальный другой курс фильтра', 'en' => 'Unique Course Filter Other'],
            'name_translations' => ['ru' => 'Уникальный другой курс фильтра', 'en' => 'Unique Course Filter Other'],
            'slug' => 'unique-course-filter-other',
            'is_active' => true,
            'is_visible_on_site' => true,
        ]);

        TrainingGroup::factory()->publicVisible()->emptyCapacity()->create([
            'branch_id' => $matchingBranch->id,
            'training_program_id' => $matchingProgram->id,
            'course_category_id' => $matchingCategory->id,
            'code' => 'COURSE-FILTER-001',
        ]);
        TrainingGroup::factory()->publicVisible()->emptyCapacity()->create([
            'branch_id' => $otherBranch->id,
            'training_program_id' => $otherProgram->id,
            'course_category_id' => $otherCategory->id,
            'code' => 'COURSE-OTHER-001',
        ]);

        $contextQuery = http_build_query([
            'country' => 'Course Filterland',
            'city' => 'Course City',
        ]);

        $this->get(route('website.courses.index', [
            'country' => 'Course Filterland',
            'city' => 'Course City',
            'category' => 'course-filter-category',
        ]))
            ->assertOk()
            ->assertSee('Уникальный курс фильтра курсов')
            ->assertSee('Город фильтра курсов')
            ->assertDontSee('Уникальный другой курс фильтра')
            ->assertDontSee('Другой город курсов')
            ->assertSee(route('website.courses.show', $matchingProgram).'?'.$contextQuery);
    }

    public function test_course_detail_form_locks_course_and_branch_from_page_context(): void
    {
        $this->seed();

        $program = TrainingProgram::query()
            ->where('slug', 'category-b-manual')
            ->firstOrFail();
        $branch = Branch::query()
            ->where('slug', 'vilnius-main')
            ->firstOrFail();

        $this->get(route('website.courses.show', $program).'?'.http_build_query([
            'country' => $branch->country,
            'city' => $branch->city,
        ]))
            ->assertOk()
            ->assertSee(tkey('website.forms.context.selected_course', [], 'ru'))
            ->assertSee(tkey('website.forms.context.selected_branch', [], 'ru'))
            ->assertSee('type="hidden" name="course_id" value="'.$program->id.'"', false)
            ->assertSee('type="hidden" name="branch_id" value="'.$branch->id.'"', false)
            ->assertDontSee('<select name="course_id"', false)
            ->assertDontSee('<select name="branch_id"', false);
    }

    public function test_pricing_page_filters_courses_and_packages_by_visible_fields(): void
    {
        $this->seed();

        $matchingCategory = CourseCategory::factory()->create([
            'code' => 'pricing_filter_category',
            'slug' => 'pricing-filter-category',
            'name_translations' => ['ru' => 'Фильтр цен категория', 'en' => 'Pricing filter category'],
            'is_active' => true,
            'is_visible_on_site' => true,
        ]);
        $otherCategory = CourseCategory::factory()->create([
            'code' => 'pricing_other_category',
            'slug' => 'pricing-other-category',
            'name_translations' => ['ru' => 'Другая ценовая категория', 'en' => 'Other pricing category'],
            'is_active' => true,
            'is_visible_on_site' => true,
        ]);
        $matchingCourse = Course::factory()->create([
            'course_category_id' => $matchingCategory->id,
            'title' => 'Unique Pricing Filter Course',
            'title_translations' => ['ru' => 'Уникальный курс цен', 'en' => 'Unique Pricing Filter Course'],
            'name_translations' => ['ru' => 'Уникальный курс цен', 'en' => 'Unique Pricing Filter Course'],
            'short_description' => 'Unique pricing matching description',
            'short_description_translations' => ['ru' => 'Уникальное описание подходящей цены', 'en' => 'Unique pricing matching description'],
            'slug' => 'unique-pricing-filter-course',
            'format' => 'online',
            'duration_weeks' => 4,
            'theory_hours' => 12,
            'practice_hours' => 6,
            'price_cents' => 70000,
            'price' => 700.00,
            'is_active' => true,
            'is_visible_on_site' => true,
        ]);
        $otherCourse = Course::factory()->create([
            'course_category_id' => $otherCategory->id,
            'title' => 'Unique Pricing Other Course',
            'title_translations' => ['ru' => 'Уникальный другой курс цен', 'en' => 'Unique Pricing Other Course'],
            'name_translations' => ['ru' => 'Уникальный другой курс цен', 'en' => 'Unique Pricing Other Course'],
            'short_description' => 'Unique pricing other description',
            'short_description_translations' => ['ru' => 'Уникальное описание другой цены', 'en' => 'Unique pricing other description'],
            'slug' => 'unique-pricing-other-course',
            'format' => 'individual',
            'duration_weeks' => 12,
            'theory_hours' => 4,
            'practice_hours' => 2,
            'price_cents' => 150000,
            'price' => 1500.00,
            'is_active' => true,
            'is_visible_on_site' => true,
        ]);

        PricingPackage::factory()->create([
            'course_id' => $matchingCourse->id,
            'course_category_id' => $matchingCategory->id,
            'slug' => 'unique-pricing-filter-package',
            'name_translations' => ['ru' => 'Уникальный пакет цен', 'en' => 'Unique Pricing Filter Package'],
            'price' => 700.00,
            'theory_hours' => 12,
            'practice_hours' => 6,
            'is_active' => true,
            'is_visible_on_site' => true,
        ]);
        PricingPackage::factory()->create([
            'course_id' => $otherCourse->id,
            'course_category_id' => $otherCategory->id,
            'slug' => 'unique-pricing-other-package',
            'name_translations' => ['ru' => 'Уникальный другой пакет цен', 'en' => 'Unique Pricing Other Package'],
            'price' => 1500.00,
            'theory_hours' => 4,
            'practice_hours' => 2,
            'is_active' => true,
            'is_visible_on_site' => true,
        ]);

        $this->get(route('website.pricing', [
            'course' => 'unique-pricing-filter-course',
            'category' => 'pricing-filter-category',
            'format' => 'online',
            'duration' => '4',
            'theory_min' => '12',
            'practice_min' => '6',
            'price_min' => '600',
            'price_max' => '800',
        ]))
            ->assertOk()
            ->assertSee('Уникальный курс цен')
            ->assertSee('Уникальное описание подходящей цены')
            ->assertSee('Уникальный пакет цен')
            ->assertDontSee('Уникальное описание другой цены')
            ->assertDontSee('Уникальный другой пакет цен')
            ->assertSee(tkey('website.prices.filters.active', [], 'ru'));
    }

    public function test_pricing_page_filters_courses_packages_groups_and_branches_by_location(): void
    {
        $this->seed();

        $matchingBranch = Branch::factory()->create([
            'slug' => 'pricing-location-branch',
            'name' => 'Pricing Location Branch',
            'name_translations' => ['ru' => 'Ценовой филиал города', 'en' => 'Pricing Location Branch'],
            'country' => 'Price Filterland',
            'country_translations' => ['ru' => 'Страна ценового фильтра', 'en' => 'Price Filterland'],
            'city' => 'Price City',
            'city_translations' => ['ru' => 'Город ценового фильтра', 'en' => 'Price City'],
            'is_active' => true,
            'is_visible_on_site' => true,
        ]);
        $otherBranch = Branch::factory()->create([
            'slug' => 'pricing-other-branch',
            'name' => 'Pricing Other Branch',
            'name_translations' => ['ru' => 'Другой ценовой филиал', 'en' => 'Pricing Other Branch'],
            'country' => 'Price Otherland',
            'country_translations' => ['ru' => 'Другая страна цен', 'en' => 'Price Otherland'],
            'city' => 'Price Other City',
            'city_translations' => ['ru' => 'Другой город цен', 'en' => 'Price Other City'],
            'is_active' => true,
            'is_visible_on_site' => true,
        ]);
        $matchingCourse = Course::factory()->create([
            'title' => 'Unique Pricing Location Course',
            'title_translations' => ['ru' => 'Уникальный курс цен по городу', 'en' => 'Unique Pricing Location Course'],
            'name_translations' => ['ru' => 'Уникальный курс цен по городу', 'en' => 'Unique Pricing Location Course'],
            'short_description_translations' => ['ru' => 'Описание ценового курса по городу', 'en' => 'Pricing location course description'],
            'slug' => 'unique-pricing-location-course',
            'is_active' => true,
            'is_visible_on_site' => true,
        ]);
        $otherCourse = Course::factory()->create([
            'title' => 'Unique Pricing Other Location Course',
            'title_translations' => ['ru' => 'Уникальный другой курс цен по городу', 'en' => 'Unique Pricing Other Location Course'],
            'name_translations' => ['ru' => 'Уникальный другой курс цен по городу', 'en' => 'Unique Pricing Other Location Course'],
            'short_description_translations' => ['ru' => 'Описание другого ценового курса по городу', 'en' => 'Other pricing location course description'],
            'slug' => 'unique-pricing-other-location-course',
            'is_active' => true,
            'is_visible_on_site' => true,
        ]);

        PricingPackage::factory()->create([
            'course_id' => $matchingCourse->id,
            'slug' => 'unique-pricing-location-package',
            'name_translations' => ['ru' => 'Уникальный пакет цен по городу', 'en' => 'Unique Pricing Location Package'],
            'is_active' => true,
            'is_visible_on_site' => true,
        ]);
        PricingPackage::factory()->create([
            'course_id' => $otherCourse->id,
            'slug' => 'unique-pricing-other-location-package',
            'name_translations' => ['ru' => 'Уникальный другой пакет цен по городу', 'en' => 'Unique Pricing Other Location Package'],
            'is_active' => true,
            'is_visible_on_site' => true,
        ]);

        TrainingGroup::factory()->publicVisible()->emptyCapacity()->create([
            'branch_id' => $matchingBranch->id,
            'training_program_id' => $matchingCourse->id,
            'code' => 'PRICE-LOCATION-001',
        ]);
        TrainingGroup::factory()->publicVisible()->emptyCapacity()->create([
            'branch_id' => $otherBranch->id,
            'training_program_id' => $otherCourse->id,
            'code' => 'PRICE-OTHER-001',
        ]);

        $contextQuery = http_build_query([
            'country' => 'Price Filterland',
            'city' => 'Price City',
        ]);

        $this->get(route('website.pricing', [
            'country' => 'Price Filterland',
            'city' => 'Price City',
        ]))
            ->assertOk()
            ->assertSee('Уникальный курс цен по городу')
            ->assertSee('Уникальный пакет цен по городу')
            ->assertSee('Ценовой филиал города')
            ->assertSee(route('website.courses.show', $matchingCourse).'?'.$contextQuery)
            ->assertDontSee('Уникальный другой курс цен по городу')
            ->assertDontSee('Уникальный другой пакет цен по городу')
            ->assertDontSee('Другой ценовой филиал');
    }

    public function test_public_form_validation_errors_are_translated(): void
    {
        $this->seed();

        $program = TrainingProgram::query()
            ->where('slug', 'category-b-manual')
            ->firstOrFail();
        $branch = Branch::query()
            ->where('slug', 'vilnius-main')
            ->firstOrFail();

        $this->from(route('website.home'))
            ->post(route('website.leads.store'), [
                'course_id' => $program->id,
                'branch_id' => $branch->id,
                'full_name' => 'No Consent',
                'phone' => '+370 600 44444',
                'preferred_format' => 'mixed',
                'preferred_language' => 'ru',
            ])
            ->assertRedirect(route('website.home'))
            ->assertSessionHasErrors([
                'consent_accepted' => tkey('website.validation.consent_required', [], 'ru'),
            ]);
    }

    public function test_application_form_returns_localized_json_validation_errors(): void
    {
        $this->seed();

        $program = TrainingProgram::query()
            ->where('slug', 'category-b-manual')
            ->firstOrFail();

        foreach (['ru', 'en', 'lt', 'pl'] as $locale) {
            $this->postJson(route('website.leads.store'), [
                'course_id' => $program->id,
                'full_name' => 'Realtime Validation',
                'phone' => '+370 600 55555',
                'preferred_format' => 'mixed',
                'preferred_language' => $locale,
                'locale' => $locale,
            ])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['branch_id', 'consent_accepted'])
                ->assertJsonPath('errors.branch_id.0', tkey('website.validation.branch_required', [], $locale))
                ->assertJsonPath('errors.consent_accepted.0', tkey('website.validation.consent_required', [], $locale));
        }
    }

    public function test_application_form_json_success_returns_redirect_payload(): void
    {
        Notification::fake();
        $this->seed();

        $program = TrainingProgram::query()
            ->where('slug', 'category-b-manual')
            ->firstOrFail();
        $branch = Branch::query()
            ->where('slug', 'vilnius-main')
            ->firstOrFail();

        $this->postJson(route('website.leads.store'), [
            'course_id' => $program->id,
            'branch_id' => $branch->id,
            'full_name' => 'Ajax Course Lead',
            'phone' => '+370 600 66666',
            'preferred_format' => 'mixed',
            'preferred_language' => 'en',
            'consent_accepted' => '1',
            'form_name' => 'ajax_course_detail_application',
            'locale' => 'en',
        ])
            ->assertOk()
            ->assertJsonPath('message', tkey('website.forms.messages.success', [], 'en'))
            ->assertJsonPath('redirect', route('website.thank_you'));

        $this->assertDatabaseHas('marketing_leads', [
            'full_name' => 'Ajax Course Lead',
            'form_name' => 'ajax_course_detail_application',
            'locale' => 'en',
        ]);
    }
}
