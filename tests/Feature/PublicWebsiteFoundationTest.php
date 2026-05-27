<?php

namespace Tests\Feature;

use App\Enums\GroupStatus;
use App\Models\Branch;
use App\Models\MarketingLead;
use App\Models\PricingPackage;
use App\Models\TrainingGroup;
use App\Models\TrainingProgram;
use App\Models\User;
use App\Notifications\EnrollmentLeadAutoReplyNotification;
use App\Notifications\EnrollmentLeadSubmittedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PublicWebsiteFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_website_pages_render_seeded_multilingual_content(): void
    {
        $this->seed();

        $program = TrainingProgram::query()
            ->where('slug', 'category-b-manual')
            ->firstOrFail();
        $branch = Branch::query()
            ->where('slug', 'vilnius-main')
            ->firstOrFail();

        collect([
            route('site.home') => tkey('website.home.programs.title', [], 'ru'),
            route('site.prices') => tkey('website.prices.title', [], 'ru'),
            route('site.contacts') => tkey('website.contacts.title', [], 'ru'),
            route('site.thanks') => tkey('website.thanks.title', [], 'ru'),
            route('site.courses.show', $program) => $program->displayTitle(),
            route('site.branches.show', ['branch' => $branch->slug]) => $branch->displayName(),
            route('site.instructors') => tkey('website.instructors.title', [], 'ru'),
            route('site.fleet') => tkey('website.vehicles.title', [], 'ru'),
            route('site.reviews') => tkey('website.reviews.title', [], 'ru'),
        ])->each(function (string $needle, string $url): void {
            $this->get($url)
                ->assertOk()
                ->assertSee($needle);
        });

        $this->get(route('site.prices'))
            ->assertOk()
            ->assertSee(tkey('website.prices.packages.title', [], 'ru'))
            ->assertSee(PricingPackage::query()->where('slug', 'category-b-premium')->firstOrFail()->displayName());
    }

    public function test_enrollment_form_creates_crm_lead_with_site_tracking(): void
    {
        Notification::fake();
        $this->seed();

        $admin = User::query()
            ->where('email', 'admin@example.com')
            ->firstOrFail();
        $program = TrainingProgram::query()
            ->where('slug', 'category-b-manual')
            ->firstOrFail();
        $branch = Branch::query()
            ->where('slug', 'vilnius-main')
            ->firstOrFail();
        $group = TrainingGroup::query()
            ->where('code', 'B-VNO-001')
            ->firstOrFail();

        $this->get('/?utm_source=google&utm_medium=cpc&utm_campaign=block1&source=website');

        $this->post(route('site.apply.store'), [
            'training_program_id' => $program->id,
            'branch_id' => $branch->id,
            'training_group_id' => $group->id,
            'first_name' => 'Ieva',
            'last_name' => 'Norkute',
            'email' => 'ieva.block1@example.com',
            'phone' => '+370 600 44444',
            'messenger' => 'WhatsApp',
            'city' => 'Vilnius',
            'preferred_format' => 'mixed',
            'preferred_language' => 'en',
            'preferred_time' => 'Weekday evenings',
            'budget_eur' => '1450',
            'message' => 'I want to join the next group.',
            'privacy_consent' => '1',
        ])
            ->assertRedirect(route('site.thanks'))
            ->assertSessionHasNoErrors();

        $lead = MarketingLead::query()
            ->where('email', 'ieva.block1@example.com')
            ->firstOrFail();

        $this->assertSame('website', $lead->source);
        $this->assertSame('block1', $lead->utm_campaign);
        $this->assertSame('enrollment', $lead->form_name);
        $this->assertSame('ru', $lead->locale);
        $this->assertStringContainsString('utm_source=google', (string) $lead->landing_page);
        $this->assertSame(1, $lead->comments()->count());
        $this->assertSame(1, $lead->communications()->count());
        $this->assertSame(1, $lead->statusHistories()->count());
        $this->assertSame(1, $lead->tasks()->count());
        Notification::assertSentTo($admin, EnrollmentLeadSubmittedNotification::class);
        Notification::assertSentOnDemand(EnrollmentLeadAutoReplyNotification::class);
    }

    public function test_callback_form_creates_crm_lead_activity_records(): void
    {
        Notification::fake();
        $this->seed();

        $branch = Branch::query()
            ->where('slug', 'vilnius-main')
            ->firstOrFail();
        $program = TrainingProgram::query()
            ->where('slug', 'category-b-manual')
            ->firstOrFail();

        $this->get('/contacts?utm_source=organic&utm_campaign=callback-block');

        $this->post(route('site.callback.store'), [
            'first_name' => 'Tomas',
            'phone' => '+370 600 77777',
            'branch_id' => $branch->id,
            'training_program_id' => $program->id,
            'preferred_time' => 'Tomorrow morning',
            'source' => 'callback',
            'message' => 'Please call me back.',
            'privacy_consent' => '1',
        ])
            ->assertRedirect(route('site.thanks'))
            ->assertSessionHasNoErrors();

        $lead = MarketingLead::query()
            ->where('phone', '+370 600 77777')
            ->firstOrFail();

        $this->assertSame('callback', $lead->source);
        $this->assertSame('callback', $lead->form_name);
        $this->assertSame('callback-block', $lead->utm_campaign);
        $this->assertSame(1, $lead->comments()->count());
        $this->assertSame(1, $lead->communications()->where('channel', 'web_form')->count());
        $this->assertSame(1, $lead->statusHistories()->count());
        $this->assertSame(1, $lead->tasks()->count());
    }

    public function test_public_lead_validation_rejects_inactive_catalog_records(): void
    {
        $this->seed();

        $program = TrainingProgram::query()
            ->where('slug', 'category-b-manual')
            ->firstOrFail();
        $branch = Branch::query()
            ->where('slug', 'vilnius-main')
            ->firstOrFail();

        $branch->update(['is_active' => false]);

        $this->from(route('site.apply'))
            ->post(route('site.apply.store'), [
                'training_program_id' => $program->id,
                'branch_id' => $branch->id,
                'first_name' => 'Rasa',
                'email' => 'rasa@example.com',
                'preferred_format' => 'mixed',
                'preferred_language' => 'ru',
                'privacy_consent' => '1',
            ])
            ->assertRedirect(route('site.apply'))
            ->assertSessionHasErrors([
                'branch_id' => tkey('website.validation.branch_unavailable', [], 'ru'),
            ]);
    }

    public function test_orchid_admin_can_manage_public_website_records(): void
    {
        $this->seed();

        $admin = User::query()
            ->where('email', 'admin@example.com')
            ->firstOrFail();
        $program = TrainingProgram::query()
            ->where('slug', 'category-b-manual')
            ->firstOrFail();
        $branch = Branch::query()
            ->where('slug', 'vilnius-main')
            ->firstOrFail();
        $group = TrainingGroup::query()
            ->where('code', 'B-VNO-001')
            ->firstOrFail();
        $package = PricingPackage::query()
            ->where('slug', 'category-b-premium')
            ->firstOrFail();

        collect([
            route('platform.website.settings'),
            route('platform.website.courses'),
            route('platform.website.pricing'),
            route('platform.website.branches'),
            route('platform.website.groups'),
            route('platform.website.leads'),
        ])->each(function (string $url) use ($admin): void {
            $this->actingAs($admin)
                ->get($url)
                ->assertOk();
        });

        $this->actingAs($admin)
            ->post(route('platform.website.courses.edit', ['program' => $program, 'method' => 'save']), [
                'program' => [
                    'id' => $program->id,
                    'slug' => $program->slug,
                    'license_category' => 'B',
                    'transmission' => 'manual',
                    'theory_hours' => 42,
                    'practice_hours' => 31,
                    'duration_weeks' => 12,
                    'format' => 'mixed',
                    'price_eur' => '1310',
                    'old_price_eur' => '1450',
                    'available_languages' => "Russian\nEnglish",
                    'required_documents' => "ID card\nMedical certificate",
                    'admission_requirements' => 'Minimum age and medical eligibility.',
                    'canonical_url' => null,
                    'open_graph_image' => null,
                    'image_path' => null,
                    'sort_order' => 11,
                    'is_active' => '1',
                ],
                'title_translations' => ['ru' => 'Категория B Block 1', 'en' => 'Category B Block 1'],
                'short_description_translations' => ['ru' => 'Краткое описание курса', 'en' => 'Short course description'],
                'description_translations' => ['ru' => 'Описание курса', 'en' => 'Course description'],
                'included_items_translations' => ['ru' => 'Теория и практика', 'en' => 'Theory and practice'],
                'extra_costs_translations' => ['ru' => 'Дополнительные часы отдельно', 'en' => 'Extra hours separately'],
                'theory_program_translations' => ['ru' => 'Теория ПДД', 'en' => 'Traffic theory'],
                'practice_program_translations' => ['ru' => 'Практика в городе', 'en' => 'City practice'],
                'seo_title_translations' => ['ru' => 'SEO курс', 'en' => 'SEO course'],
                'seo_description_translations' => ['ru' => 'SEO описание', 'en' => 'SEO description'],
                'og_title_translations' => ['ru' => 'OG курс', 'en' => 'OG course'],
                'og_description_translations' => ['ru' => 'OG описание', 'en' => 'OG description'],
            ])
            ->assertRedirect(route('platform.website.courses'))
            ->assertSessionHasNoErrors();

        $this->actingAs($admin)
            ->post(route('platform.website.branches.edit', ['branch' => $branch, 'method' => 'save']), [
                'branch' => [
                    'id' => $branch->id,
                    'slug' => $branch->slug,
                    'phone' => $branch->phone,
                    'email' => $branch->email,
                    'latitude' => null,
                    'longitude' => null,
                    'canonical_url' => null,
                    'open_graph_image' => null,
                    'sort_order' => 12,
                    'is_active' => '1',
                ],
                'name_translations' => ['ru' => 'Вильнюс Block 1', 'en' => 'Vilnius Block 1'],
                'city_translations' => ['ru' => 'Вильнюс', 'en' => 'Vilnius'],
                'address_translations' => ['ru' => 'Тестовый адрес', 'en' => 'Test address'],
                'description_translations' => ['ru' => 'Описание филиала', 'en' => 'Branch description'],
                'working_hours_translations' => ['ru' => 'Пн-Пт 09:00-18:00', 'en' => 'Mon-Fri 09:00-18:00'],
                'seo_title_translations' => ['ru' => 'SEO филиал', 'en' => 'SEO branch'],
                'seo_description_translations' => ['ru' => 'SEO описание филиала', 'en' => 'SEO branch description'],
                'og_title_translations' => ['ru' => 'OG филиал', 'en' => 'OG branch'],
                'og_description_translations' => ['ru' => 'OG описание филиала', 'en' => 'OG branch description'],
            ])
            ->assertRedirect(route('platform.website.branches'))
            ->assertSessionHasNoErrors();

        $this->actingAs($admin)
            ->post(route('platform.website.groups.edit', ['group' => $group, 'method' => 'save']), [
                'group' => [
                    'id' => $group->id,
                    'branch_id' => $branch->id,
                    'training_program_id' => $program->id,
                    'instructor_id' => $group->instructor_id,
                    'code' => $group->code,
                    'status' => GroupStatus::Recruiting->value,
                    'capacity' => 14,
                    'places_taken' => 6,
                    'starts_on' => now()->addDays(20)->toDateString(),
                    'ends_on' => now()->addMonths(4)->toDateString(),
                    'meeting_days' => 'tuesday, thursday',
                    'meeting_time' => '18:30',
                    'classroom' => 'Room 4',
                    'is_visible_on_site' => '1',
                ],
                'name_translations' => ['ru' => 'Вечерняя группа Block 1', 'en' => 'Evening Group Block 1'],
            ])
            ->assertRedirect(route('platform.website.groups'))
            ->assertSessionHasNoErrors();

        $this->actingAs($admin)
            ->post(route('platform.website.pricing.edit', ['pricingPackage' => $package, 'method' => 'save']), [
                'package' => [
                    'id' => $package->id,
                    'course_id' => $program->id,
                    'course_category_id' => $program->course_category_id,
                    'code' => $package->code,
                    'slug' => $package->slug,
                    'price' => '1510.50',
                    'old_price' => '1610.00',
                    'currency' => 'EUR',
                    'theory_hours' => '42',
                    'practice_hours' => '30',
                    'is_active' => '1',
                    'is_visible_on_site' => '1',
                    'is_featured' => '1',
                    'sort_order' => 15,
                ],
                'name_translations' => ['ru' => 'Premium Block 1', 'en' => 'Premium Block 1'],
                'description_translations' => ['ru' => 'Пакет для сайта', 'en' => 'Website package'],
                'features_translations' => ['ru' => "Теория\nПрактика", 'en' => "Theory\nPractice"],
            ])
            ->assertRedirect(route('platform.website.pricing'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('training_programs', [
            'id' => $program->id,
            'title' => 'Категория B Block 1',
            'price_cents' => 131000,
            'sort_order' => 11,
        ]);
        $this->assertDatabaseHas('branches', [
            'id' => $branch->id,
            'name' => 'Вильнюс Block 1',
            'sort_order' => 12,
        ]);
        $this->assertDatabaseHas('training_groups', [
            'id' => $group->id,
            'name' => 'Вечерняя группа Block 1',
            'places_taken' => 6,
            'is_visible_on_site' => true,
        ]);

        $package->refresh();

        $this->assertSame('Premium Block 1', $package->displayName('en'));
        $this->assertSame(['Theory', 'Practice'], $package->displayFeatures('en'));
        $this->assertSame(1510.50, (float) $package->price);
        $this->assertTrue($package->is_featured);
    }
}
