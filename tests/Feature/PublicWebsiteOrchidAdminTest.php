<?php

namespace Tests\Feature;

use App\Actions\CreateOrUpdateBranchAction;
use App\Actions\CreateOrUpdateCourseAction;
use App\Actions\CreateOrUpdateSitePageAction;
use App\Enums\GroupStatus;
use App\Models\Branch;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\Faq;
use App\Models\Lead;
use App\Models\PricingPackage;
use App\Models\SitePage;
use App\Models\Testimonial;
use App\Models\TrainingGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
use Tests\TestCase;

class PublicWebsiteOrchidAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_with_permission_can_access_website_screens(): void
    {
        $this->seed();

        $admin = User::query()->where('email', 'admin@example.com')->firstOrFail();
        $page = SitePage::query()->firstOrFail();
        $category = CourseCategory::query()->firstOrFail();
        $course = Course::query()->firstOrFail();
        $package = PricingPackage::query()->firstOrFail();
        $branch = Branch::query()->firstOrFail();
        $faq = Faq::factory()->create();
        $testimonial = Testimonial::factory()->create();

        collect([
            route('platform.website.pages') => tkey('website.admin.pages.title', [], 'ru'),
            route('platform.website.pages.create') => tkey('website.admin.pages.create_title', [], 'ru'),
            route('platform.website.pages.edit', $page) => tkey('website.admin.pages.edit_title', [], 'ru'),
            route('platform.website.course-categories') => tkey('website.admin.course_categories.title', [], 'ru'),
            route('platform.website.course-categories.create') => tkey('website.admin.course_categories.create_title', [], 'ru'),
            route('platform.website.course-categories.edit', $category) => tkey('website.admin.course_categories.edit_title', [], 'ru'),
            route('platform.website.courses') => tkey('website.admin.courses.title', [], 'ru'),
            route('platform.website.courses.create') => tkey('website.admin.courses.create_title', [], 'ru'),
            route('platform.website.courses.edit', $course) => tkey('website.admin.courses.edit_title', [], 'ru'),
            route('platform.website.pricing') => tkey('website.admin.pricing.title', [], 'ru'),
            route('platform.website.pricing.create') => tkey('website.admin.pricing.create_title', [], 'ru'),
            route('platform.website.pricing.edit', $package) => tkey('website.admin.pricing.edit_title', [], 'ru'),
            route('platform.website.branches') => tkey('website.admin.branches.title', [], 'ru'),
            route('platform.website.branches.create') => tkey('website.admin.branches.create_title', [], 'ru'),
            route('platform.website.branches.edit', $branch) => tkey('website.admin.branches.edit_title', [], 'ru'),
            route('platform.website.groups') => tkey('website.admin.groups.title', [], 'ru'),
            route('platform.website.faq') => tkey('website.admin.faq.title', [], 'ru'),
            route('platform.website.faq.create') => tkey('website.admin.faq.create_title', [], 'ru'),
            route('platform.website.faq.edit', $faq) => tkey('website.admin.faq.edit_title', [], 'ru'),
            route('platform.website.testimonials') => tkey('website.admin.testimonials.title', [], 'ru'),
            route('platform.website.testimonials.create') => tkey('website.admin.testimonials.create_title', [], 'ru'),
            route('platform.website.testimonials.edit', $testimonial) => tkey('website.admin.testimonials.edit_title', [], 'ru'),
            route('platform.website.leads') => tkey('website.admin.leads.title', [], 'ru'),
            route('platform.website.settings') => tkey('website.admin.settings.title', [], 'ru'),
        ])->each(function (string $title, string $url) use ($admin): void {
            $this->actingAs($admin)
                ->get($url)
                ->assertOk()
                ->assertSee($title);
        });
    }

    public function test_group_list_exposes_create_action(): void
    {
        $this->seed();

        $admin = User::query()->where('email', 'admin@example.com')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('platform.website.groups'))
            ->assertOk()
            ->assertSee(tkey('website.admin.groups.create_title', [], 'ru'))
            ->assertSee(route('platform.website.groups.create'), false);
    }

    public function test_group_create_screen_creates_group_through_action(): void
    {
        $this->seed();

        $admin = User::query()->where('email', 'admin@example.com')->firstOrFail();
        $course = Course::query()->firstOrFail();
        $branch = Branch::query()->firstOrFail();

        $this->actingAs($admin)
            ->post(route('platform.website.groups.create', ['method' => 'save']), [
                'group' => [
                    'branch_id' => $branch->id,
                    'training_program_id' => $course->id,
                    'instructor_id' => null,
                    'code' => 'WEB-CREATE-001',
                    'status' => GroupStatus::Recruiting->value,
                    'capacity' => 12,
                    'places_taken' => 0,
                    'starts_on' => now()->addMonth()->toDateString(),
                    'ends_on' => now()->addMonths(4)->toDateString(),
                    'meeting_days' => 'monday, wednesday',
                    'meeting_time' => '18:00',
                    'end_time' => '20:00',
                    'classroom' => 'Class 1',
                    'is_visible_on_site' => '1',
                ],
                'name_translations' => [
                    'ru' => 'Новая группа сайта',
                    'en' => 'New website group',
                ],
            ])
            ->assertRedirect(route('platform.website.groups'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('training_groups', [
            'code' => 'WEB-CREATE-001',
            'branch_id' => $branch->id,
            'training_program_id' => $course->id,
            'name' => 'Новая группа сайта',
            'capacity' => 12,
            'is_visible_on_site' => true,
        ]);
    }

    public function test_user_without_permission_cannot_access_website_screens(): void
    {
        $this->seed();

        $user = User::factory()->create();
        $user->forceFill(['permissions' => ['platform.index' => true]])->save();

        $this->actingAs($user)
            ->get(route('platform.website.pages'))
            ->assertForbidden();
    }

    public function test_course_screen_saves_through_action(): void
    {
        $this->seed();

        $admin = User::query()->where('email', 'admin@example.com')->firstOrFail();
        $course = Course::query()->firstOrFail();

        $this->mock(CreateOrUpdateCourseAction::class, function (MockInterface $mock) use ($course): void {
            $mock->shouldReceive('handle')
                ->once()
                ->withArgs(fn (?Course $model, array $payload): bool => $model?->is($course)
                    && ($payload['name_translations']['ru'] ?? null) === 'Курс через Action')
                ->andReturn($course);
        });

        $this->actingAs($admin)
            ->post(route('platform.website.courses.edit', ['program' => $course, 'method' => 'save']), [
                'course_category_id' => $course->course_category_id,
                'id' => $course->id,
                'code' => $course->code,
                'slug' => $course->slug,
                'license_category' => 'B',
                'transmission' => 'manual',
                'name_translations' => ['ru' => 'Курс через Action', 'en' => 'Course through Action'],
                'short_description_translations' => ['ru' => 'Кратко', 'en' => 'Short'],
                'description_translations' => ['ru' => 'Описание курса', 'en' => 'Course description'],
                'price' => '1200.00',
                'currency' => 'EUR',
                'theory_hours' => 40,
                'practice_hours' => 30,
                'duration_weeks' => 10,
                'format' => 'mixed',
                'is_active' => '1',
                'is_visible_on_site' => '1',
                'is_featured' => '0',
                'sort_order' => 10,
            ])
            ->assertRedirect(route('platform.website.courses'))
            ->assertSessionHasNoErrors();
    }

    public function test_branch_screen_saves_through_action(): void
    {
        $this->seed();

        $admin = User::query()->where('email', 'admin@example.com')->firstOrFail();
        $branch = Branch::query()->firstOrFail();

        $this->mock(CreateOrUpdateBranchAction::class, function (MockInterface $mock) use ($branch): void {
            $mock->shouldReceive('handle')
                ->once()
                ->withArgs(fn (?Branch $model, array $payload): bool => $model?->is($branch)
                    && ($payload['name_translations']['ru'] ?? null) === 'Филиал через Action')
                ->andReturn($branch);
        });

        $this->actingAs($admin)
            ->post(route('platform.website.branches.edit', ['branch' => $branch, 'method' => 'save']), [
                'code' => $branch->code,
                'id' => $branch->id,
                'slug' => $branch->slug,
                'name_translations' => ['ru' => 'Филиал через Action', 'en' => 'Branch through Action'],
                'city_translations' => ['ru' => 'Вильнюс', 'en' => 'Vilnius'],
                'address_translations' => ['ru' => 'Тестовый адрес', 'en' => 'Test address'],
                'phone' => $branch->phone,
                'email' => $branch->email,
                'is_active' => '1',
                'is_visible_on_site' => '1',
                'sort_order' => 12,
            ])
            ->assertRedirect(route('platform.website.branches'))
            ->assertSessionHasNoErrors();
    }

    public function test_page_screen_saves_through_action(): void
    {
        $this->seed();

        $admin = User::query()->where('email', 'admin@example.com')->firstOrFail();
        $page = SitePage::query()->where('type', 'home')->firstOrFail();

        $this->mock(CreateOrUpdateSitePageAction::class, function (MockInterface $mock) use ($page): void {
            $mock->shouldReceive('handle')
                ->once()
                ->withArgs(fn (?SitePage $model, array $payload): bool => $model?->is($page)
                    && ($payload['title_translations']['ru'] ?? null) === 'Страница через Action')
                ->andReturn($page);
        });

        $this->actingAs($admin)
            ->post(route('platform.website.pages.edit', ['page' => $page, 'method' => 'save']), [
                'type' => 'home',
                'id' => $page->id,
                'slug' => $page->slug,
                'title_translations' => ['ru' => 'Страница через Action', 'en' => 'Page through Action'],
                'content_translations' => ['ru' => 'Контент страницы', 'en' => 'Page content'],
                'is_active' => '1',
                'is_indexable' => '1',
                'sort_order' => 1,
            ])
            ->assertRedirect(route('platform.website.pages'))
            ->assertSessionHasNoErrors();
    }

    public function test_marketing_fields_are_hidden_without_marketing_permission(): void
    {
        $this->seed();

        Lead::factory()
            ->fromWebsite()
            ->withUtm()
            ->create([
                'first_name' => 'Marketing',
                'last_name' => 'Hidden',
                'utm_source' => 'google-hidden',
                'utm_campaign' => 'website-hidden-campaign',
            ]);

        $user = User::factory()->create();
        $user->forceFill(['permissions' => [
            'platform.index' => true,
            'website.view_leads' => true,
        ]])->save();

        $this->actingAs($user)
            ->get(route('platform.website.leads'))
            ->assertOk()
            ->assertSee('Marketing Hidden')
            ->assertDontSee(tkey('crm.leads.fields.utm_source', [], 'ru'))
            ->assertDontSee('google-hidden')
            ->assertDontSee('website-hidden-campaign');

        $marketingUser = User::factory()->create();
        $marketingUser->forceFill(['permissions' => [
            'platform.index' => true,
            'website.view_leads' => true,
            'website.view_marketing' => true,
        ]])->save();

        $this->actingAs($marketingUser)
            ->get(route('platform.website.leads'))
            ->assertOk()
            ->assertSee(tkey('crm.leads.fields.utm_source', [], 'ru'))
            ->assertSee('google-hidden')
            ->assertSee('website-hidden-campaign');

        $crmMarketingUser = User::factory()->create();
        $crmMarketingUser->forceFill(['permissions' => [
            'platform.index' => true,
            'website.view_leads' => true,
            'crm.leads.view_marketing' => true,
        ]])->save();

        $this->actingAs($crmMarketingUser)
            ->get(route('platform.website.leads'))
            ->assertOk()
            ->assertSee(tkey('crm.leads.fields.utm_source', [], 'ru'))
            ->assertSee(tkey('crm.leads.fields.form_page', [], 'ru'))
            ->assertSee('google-hidden')
            ->assertSee('website-hidden-campaign');
    }
}
