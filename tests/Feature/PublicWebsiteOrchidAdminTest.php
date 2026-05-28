<?php

namespace Tests\Feature;

use App\Actions\CreateOrUpdateBranchAction;
use App\Actions\CreateOrUpdateCourseAction;
use App\Actions\CreateOrUpdateCourseCategoryAction;
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

    public function test_faq_list_has_boundary_aware_order_controls_without_numeric_sorting(): void
    {
        $this->seed();

        $admin = User::query()->where('email', 'admin@example.com')->firstOrFail();
        Faq::query()->delete();

        $first = Faq::factory()->create([
            'question_translations' => ['ru' => 'Первый вопрос', 'en' => 'First question'],
            'sort_order' => 12345,
        ]);
        $middle = Faq::factory()->create([
            'question_translations' => ['ru' => 'Средний вопрос', 'en' => 'Middle question'],
            'sort_order' => 23456,
        ]);
        $last = Faq::factory()->create([
            'question_translations' => ['ru' => 'Последний вопрос', 'en' => 'Last question'],
            'sort_order' => 34567,
        ]);

        $baseUrl = route('platform.website.faq');

        $this->actingAs($admin)
            ->get($baseUrl)
            ->assertOk()
            ->assertSee('Первый вопрос')
            ->assertSee('Средний вопрос')
            ->assertSee('Последний вопрос')
            ->assertSee(tkey('website.admin.faq.fields.position', [], 'ru'))
            ->assertDontSee(tkey('website.admin.fields.sort_order', [], 'ru'))
            ->assertDontSee('>12345<', false)
            ->assertDontSee('>23456<', false)
            ->assertDontSee('>34567<', false)
            ->assertDontSee($this->orderActionFormUrl($baseUrl, 'moveUp', $first->id), false)
            ->assertSee($this->orderActionFormUrl($baseUrl, 'moveDown', $first->id), false)
            ->assertSee($this->orderActionFormUrl($baseUrl, 'moveUp', $middle->id), false)
            ->assertSee($this->orderActionFormUrl($baseUrl, 'moveDown', $middle->id), false)
            ->assertSee($this->orderActionFormUrl($baseUrl, 'moveUp', $last->id), false)
            ->assertDontSee($this->orderActionFormUrl($baseUrl, 'moveDown', $last->id), false);
    }

    public function test_faq_order_actions_reorder_items_and_ignore_boundaries(): void
    {
        $this->seed();

        $admin = User::query()->where('email', 'admin@example.com')->firstOrFail();
        Faq::query()->delete();

        $first = Faq::factory()->create(['sort_order' => 10]);
        $middle = Faq::factory()->create(['sort_order' => 20]);
        $last = Faq::factory()->create(['sort_order' => 30]);

        $this->actingAs($admin)
            ->post(route('platform.website.faq', ['method' => 'moveUp']), ['id' => $first->id])
            ->assertRedirect(route('platform.website.faq'));
        $this->assertSame([$first->id, $middle->id, $last->id], $this->orderedFaqIds());

        $this->actingAs($admin)
            ->post(route('platform.website.faq', ['method' => 'moveDown']), ['id' => $last->id])
            ->assertRedirect(route('platform.website.faq'));
        $this->assertSame([$first->id, $middle->id, $last->id], $this->orderedFaqIds());

        $this->actingAs($admin)
            ->post(route('platform.website.faq', ['method' => 'moveDown']), ['id' => $first->id])
            ->assertRedirect(route('platform.website.faq'));
        $this->assertSame([$middle->id, $first->id, $last->id], $this->orderedFaqIds());

        $this->actingAs($admin)
            ->post(route('platform.website.faq', ['method' => 'moveUp']), ['id' => $last->id])
            ->assertRedirect(route('platform.website.faq'));
        $this->assertSame([$middle->id, $last->id, $first->id], $this->orderedFaqIds());
    }

    public function test_faq_edit_screen_hides_manual_sort_order_field(): void
    {
        $this->seed();

        $admin = User::query()->where('email', 'admin@example.com')->firstOrFail();
        $faq = Faq::factory()->create(['sort_order' => 98765]);

        $this->actingAs($admin)
            ->get(route('platform.website.faq.edit', $faq))
            ->assertOk()
            ->assertDontSee(tkey('website.admin.fields.sort_order', [], 'ru'))
            ->assertDontSee('name="sort_order"', false)
            ->assertDontSee('value="98765"', false);
    }

    public function test_faq_create_assigns_next_position_without_manual_sort_order(): void
    {
        $this->seed();

        $admin = User::query()->where('email', 'admin@example.com')->firstOrFail();
        Faq::query()->delete();
        Faq::factory()->create(['sort_order' => 50]);

        $this->actingAs($admin)
            ->post(route('platform.website.faq.create', ['method' => 'save']), [
                'is_active' => '1',
                'question_translations' => ['ru' => 'Новый вопрос', 'en' => 'New question'],
                'answer_translations' => ['ru' => 'Новый ответ', 'en' => 'New answer'],
            ])
            ->assertRedirect(route('platform.website.faq'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('faqs', [
            'sort_order' => 60,
        ]);
    }

    public function test_pricing_list_has_boundary_aware_order_controls_without_numeric_sorting(): void
    {
        $this->seed();

        $admin = User::query()->where('email', 'admin@example.com')->firstOrFail();
        PricingPackage::query()->delete();

        $course = Course::query()->firstOrFail();
        $category = CourseCategory::query()->firstOrFail();

        $first = PricingPackage::factory()->create([
            'course_id' => $course->id,
            'course_category_id' => $category->id,
            'name_translations' => ['ru' => 'Первый пакет', 'en' => 'First package'],
            'slug' => 'first-package',
            'code' => 'first_package',
            'sort_order' => 12345,
        ]);
        $middle = PricingPackage::factory()->create([
            'course_id' => $course->id,
            'course_category_id' => $category->id,
            'name_translations' => ['ru' => 'Средний пакет', 'en' => 'Middle package'],
            'slug' => 'middle-package',
            'code' => 'middle_package',
            'sort_order' => 23456,
        ]);
        $last = PricingPackage::factory()->create([
            'course_id' => $course->id,
            'course_category_id' => $category->id,
            'name_translations' => ['ru' => 'Последний пакет', 'en' => 'Last package'],
            'slug' => 'last-package',
            'code' => 'last_package',
            'sort_order' => 34567,
        ]);

        $baseUrl = route('platform.website.pricing');

        $this->actingAs($admin)
            ->get($baseUrl)
            ->assertOk()
            ->assertSee('Первый пакет')
            ->assertSee('Средний пакет')
            ->assertSee('Последний пакет')
            ->assertSee(tkey('website.admin.fields.position', [], 'ru'))
            ->assertDontSee(tkey('website.admin.fields.sort_order', [], 'ru'))
            ->assertDontSee('>12345<', false)
            ->assertDontSee('>23456<', false)
            ->assertDontSee('>34567<', false)
            ->assertDontSee($this->orderActionFormUrl($baseUrl, 'moveUp', $first->id), false)
            ->assertSee($this->orderActionFormUrl($baseUrl, 'moveDown', $first->id), false)
            ->assertSee($this->orderActionFormUrl($baseUrl, 'moveUp', $middle->id), false)
            ->assertSee($this->orderActionFormUrl($baseUrl, 'moveDown', $middle->id), false)
            ->assertSee($this->orderActionFormUrl($baseUrl, 'moveUp', $last->id), false)
            ->assertDontSee($this->orderActionFormUrl($baseUrl, 'moveDown', $last->id), false);
    }

    public function test_pricing_order_actions_reorder_items_and_ignore_boundaries(): void
    {
        $this->seed();

        $admin = User::query()->where('email', 'admin@example.com')->firstOrFail();
        PricingPackage::query()->delete();

        $first = PricingPackage::factory()->create(['sort_order' => 10]);
        $middle = PricingPackage::factory()->create(['sort_order' => 20]);
        $last = PricingPackage::factory()->create(['sort_order' => 30]);

        $this->actingAs($admin)
            ->post(route('platform.website.pricing', ['method' => 'moveUp']), ['id' => $first->id])
            ->assertRedirect(route('platform.website.pricing'));
        $this->assertSame([$first->id, $middle->id, $last->id], $this->orderedPricingPackageIds());

        $this->actingAs($admin)
            ->post(route('platform.website.pricing', ['method' => 'moveDown']), ['id' => $last->id])
            ->assertRedirect(route('platform.website.pricing'));
        $this->assertSame([$first->id, $middle->id, $last->id], $this->orderedPricingPackageIds());

        $this->actingAs($admin)
            ->post(route('platform.website.pricing', ['method' => 'moveDown']), ['id' => $first->id])
            ->assertRedirect(route('platform.website.pricing'));
        $this->assertSame([$middle->id, $first->id, $last->id], $this->orderedPricingPackageIds());

        $this->actingAs($admin)
            ->post(route('platform.website.pricing', ['method' => 'moveUp']), ['id' => $last->id])
            ->assertRedirect(route('platform.website.pricing'));
        $this->assertSame([$middle->id, $last->id, $first->id], $this->orderedPricingPackageIds());
    }

    public function test_pricing_create_assigns_next_position_without_manual_sort_order(): void
    {
        $this->seed();

        $admin = User::query()->where('email', 'admin@example.com')->firstOrFail();
        $course = Course::query()->firstOrFail();
        $category = CourseCategory::query()->firstOrFail();
        PricingPackage::query()->delete();
        PricingPackage::factory()->create(['sort_order' => 50]);

        $this->actingAs($admin)
            ->post(route('platform.website.pricing.create', ['method' => 'save']), [
                'course_id' => $course->id,
                'course_category_id' => $category->id,
                'code' => 'created_without_sort',
                'slug' => 'created-without-sort',
                'price' => '990.00',
                'currency' => 'EUR',
                'is_active' => '1',
                'is_visible_on_site' => '1',
                'is_featured' => '0',
                'name_translations' => ['ru' => 'Новый пакет', 'en' => 'New package'],
            ])
            ->assertRedirect(route('platform.website.pricing'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('pricing_packages', [
            'slug' => 'created-without-sort',
            'sort_order' => 60,
        ]);
    }

    public function test_branch_list_has_boundary_aware_order_controls_without_numeric_sorting(): void
    {
        $this->seed();

        $admin = User::query()->where('email', 'admin@example.com')->firstOrFail();
        $orderedBranches = Branch::query()->ordered()->get();
        $this->assertGreaterThanOrEqual(2, $orderedBranches->count());

        $first = $orderedBranches->first();
        $last = $orderedBranches->last();
        $baseUrl = route('platform.website.branches');

        $this->actingAs($admin)
            ->get($baseUrl)
            ->assertOk()
            ->assertSee($first->displayName())
            ->assertSee($last->displayName())
            ->assertSee(tkey('website.admin.fields.position', [], 'ru'))
            ->assertDontSee(tkey('website.admin.fields.sort_order', [], 'ru'))
            ->assertDontSee($this->orderActionFormUrl($baseUrl, 'moveUp', $first->id), false)
            ->assertSee($this->orderActionFormUrl($baseUrl, 'moveDown', $first->id), false)
            ->assertSee($this->orderActionFormUrl($baseUrl, 'moveUp', $last->id), false)
            ->assertDontSee($this->orderActionFormUrl($baseUrl, 'moveDown', $last->id), false);
    }

    public function test_branch_order_actions_reorder_items_and_ignore_boundaries(): void
    {
        $this->seed();

        $admin = User::query()->where('email', 'admin@example.com')->firstOrFail();
        $initialIds = $this->orderedBranchIds();
        $this->assertGreaterThanOrEqual(2, count($initialIds));

        $first = Branch::query()->findOrFail($initialIds[0]);
        $second = Branch::query()->findOrFail($initialIds[1]);
        $last = Branch::query()->findOrFail($initialIds[array_key_last($initialIds)]);

        $this->actingAs($admin)
            ->post(route('platform.website.branches', ['method' => 'moveUp']), ['id' => $first->id])
            ->assertRedirect(route('platform.website.branches'));
        $this->assertSame($initialIds, $this->orderedBranchIds());

        $this->actingAs($admin)
            ->post(route('platform.website.branches', ['method' => 'moveDown']), ['id' => $last->id])
            ->assertRedirect(route('platform.website.branches'));
        $this->assertSame($initialIds, $this->orderedBranchIds());

        $expectedIds = $initialIds;
        $expectedIds[0] = $second->id;
        $expectedIds[1] = $first->id;

        $this->actingAs($admin)
            ->post(route('platform.website.branches', ['method' => 'moveDown']), ['id' => $first->id])
            ->assertRedirect(route('platform.website.branches'));
        $this->assertSame($expectedIds, $this->orderedBranchIds());
    }

    public function test_branch_create_assigns_next_position_without_manual_sort_order(): void
    {
        Branch::factory()->create(['sort_order' => 50]);
        $maxSortOrder = (int) Branch::query()->max('sort_order');

        app(CreateOrUpdateBranchAction::class)->handle(null, [
            'code' => 'BRANCH_WITHOUT_SORT',
            'slug' => 'branch-without-sort',
            'phone' => '+370 600 00099',
            'email' => 'branch-without-sort@drivepro.test',
            'is_active' => true,
            'is_visible_on_site' => true,
            'is_indexable' => true,
            'name_translations' => ['ru' => 'Новый филиал', 'en' => 'New branch'],
            'city_translations' => ['ru' => 'Вильнюс', 'en' => 'Vilnius'],
            'address_translations' => ['ru' => 'Тестовый адрес 1', 'en' => 'Test address 1'],
            'description_translations' => ['ru' => 'Описание филиала', 'en' => 'Branch description'],
        ]);

        $this->assertDatabaseHas('branches', [
            'slug' => 'branch-without-sort',
            'sort_order' => $maxSortOrder + 10,
        ]);
    }

    public function test_orderable_website_lists_use_position_controls_without_numeric_sorting(): void
    {
        $this->seed();

        $admin = User::query()->where('email', 'admin@example.com')->firstOrFail();

        foreach ($this->orderableWebsiteListCases() as $case) {
            [$routeName, $modelClass] = $case;
            $baseUrl = route($routeName);
            $orderedItems = $modelClass::query()->ordered()->get();
            $first = $orderedItems->first();
            $last = $orderedItems->last();

            $this->actingAs($admin)
                ->get($baseUrl)
                ->assertOk()
                ->assertSee(tkey('website.admin.fields.position', [], 'ru'))
                ->assertDontSee(tkey('website.admin.fields.sort_order', [], 'ru'))
                ->assertDontSee($this->orderActionFormUrl($baseUrl, 'moveUp', $first->id), false)
                ->assertSee($this->orderActionFormUrl($baseUrl, 'moveDown', $first->id), false)
                ->assertSee($this->orderActionFormUrl($baseUrl, 'moveUp', $last->id), false)
                ->assertDontSee($this->orderActionFormUrl($baseUrl, 'moveDown', $last->id), false);
        }
    }

    public function test_orderable_website_edit_screens_hide_manual_sort_order_field(): void
    {
        $this->seed();

        $admin = User::query()->where('email', 'admin@example.com')->firstOrFail();

        foreach ($this->orderableWebsiteEditUrls() as $url) {
            $this->actingAs($admin)
                ->get($url)
                ->assertOk()
                ->assertDontSee(tkey('website.admin.fields.sort_order', [], 'ru'))
                ->assertDontSee('name="sort_order"', false);
        }
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

    public function test_course_category_screen_saves_through_action(): void
    {
        $this->seed();

        $admin = User::query()->where('email', 'admin@example.com')->firstOrFail();
        $category = CourseCategory::query()->firstOrFail();

        $this->mock(CreateOrUpdateCourseCategoryAction::class, function (MockInterface $mock) use ($category): void {
            $mock->shouldReceive('handle')
                ->once()
                ->withArgs(fn (?CourseCategory $model, array $payload): bool => $model?->is($category)
                    && ($payload['name_translations']['ru'] ?? null) === 'Категория через Action')
                ->andReturn($category);
        });

        $this->actingAs($admin)
            ->post(route('platform.website.course-categories.edit', ['category' => $category, 'method' => 'save']), [
                'id' => $category->id,
                'code' => $category->code,
                'slug' => $category->slug,
                'image' => $category->image,
                'icon' => $category->icon,
                'name_translations' => ['ru' => 'Категория через Action', 'en' => 'Category through Action'],
                'short_description_translations' => ['ru' => 'Краткое описание', 'en' => 'Short description'],
                'description_translations' => ['ru' => 'Описание категории', 'en' => 'Category description'],
                'seo_title_translations' => ['ru' => 'SEO категории', 'en' => 'Category SEO'],
                'seo_description_translations' => ['ru' => 'SEO описание категории', 'en' => 'Category SEO description'],
                'is_active' => '1',
                'is_visible_on_site' => '1',
                'sort_order' => 10,
            ])
            ->assertRedirect(route('platform.website.course-categories'))
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

    public function test_website_leads_screen_localizes_source_and_form_labels_for_current_locale(): void
    {
        $this->seed();

        Lead::factory()->fromWebsite()->create([
            'full_name' => 'Localized Website Lead',
            'source' => 'website',
            'form_name' => 'enrollment',
        ]);
        Lead::factory()->contactForm()->create([
            'full_name' => 'Localized Contact Lead',
            'source' => 'contact',
            'form_name' => 'contact',
        ]);

        $user = User::factory()->create();
        $user->forceFill([
            'preferred_locale' => 'lt',
            'permissions' => [
                'platform.index' => true,
                'website.view_leads' => true,
            ],
        ])->save();

        $this->actingAs($user)
            ->get(route('platform.website.leads'))
            ->assertOk()
            ->assertSee(tkey('website.admin.leads.title', [], 'lt'))
            ->assertSee(tkey('crm.leads.sources.website', [], 'lt'))
            ->assertSee(tkey('crm.leads.sources.contact', [], 'lt'))
            ->assertSee(tkey('website.forms.apply.title', [], 'lt'))
            ->assertSee(tkey('website.forms.contact.title', [], 'lt'))
            ->assertDontSee('>website<', false)
            ->assertDontSee('>contact<', false)
            ->assertDontSee('>enrollment<', false);
    }

    /**
     * @return array<int, int>
     */
    private function orderedFaqIds(): array
    {
        return Faq::query()
            ->ordered()
            ->pluck('id')
            ->all();
    }

    /**
     * @return array<int, int>
     */
    private function orderedPricingPackageIds(): array
    {
        return PricingPackage::query()
            ->ordered()
            ->pluck('id')
            ->all();
    }

    /**
     * @return array<int, int>
     */
    private function orderedBranchIds(): array
    {
        return Branch::query()
            ->ordered()
            ->pluck('id')
            ->all();
    }

    private function orderActionFormUrl(string $baseUrl, string $method, int $id): string
    {
        return 'formaction="'.$baseUrl.'/'.$method.'?id='.$id.'"';
    }

    /**
     * @return array<int, array{0: string, 1: class-string}>
     */
    private function orderableWebsiteListCases(): array
    {
        return [
            ['platform.website.pages', SitePage::class],
            ['platform.website.course-categories', CourseCategory::class],
            ['platform.website.courses', Course::class],
            ['platform.website.pricing', PricingPackage::class],
            ['platform.website.branches', Branch::class],
            ['platform.website.testimonials', Testimonial::class],
        ];
    }

    /**
     * @return array<int, string>
     */
    private function orderableWebsiteEditUrls(): array
    {
        return [
            route('platform.website.pages.edit', SitePage::query()->firstOrFail()),
            route('platform.website.course-categories.edit', CourseCategory::query()->firstOrFail()),
            route('platform.website.courses.edit', Course::query()->firstOrFail()),
            route('platform.website.pricing.edit', PricingPackage::query()->firstOrFail()),
            route('platform.website.branches.edit', Branch::query()->firstOrFail()),
            route('platform.website.testimonials.edit', Testimonial::query()->firstOrFail()),
        ];
    }
}
