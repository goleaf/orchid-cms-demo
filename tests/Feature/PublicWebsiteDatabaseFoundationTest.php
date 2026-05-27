<?php

namespace Tests\Feature;

use App\Enums\GroupStatus;
use App\Models\Branch;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\Faq;
use App\Models\Lead;
use App\Models\PricingPackage;
use App\Models\SitePage;
use App\Models\SiteSetting;
use App\Models\Testimonial;
use App\Models\TrainingGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicWebsiteDatabaseFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_website_models_can_be_created_with_translations(): void
    {
        $page = SitePage::factory()->create([
            'slug' => 'foundation-page',
            'title_translations' => ['ru' => 'Главная', 'en' => 'Home'],
        ]);
        $category = CourseCategory::factory()->create([
            'name_translations' => ['ru' => 'Категория B', 'en' => 'Category B'],
        ]);
        $course = Course::factory()->create([
            'course_category_id' => $category->id,
            'name_translations' => ['ru' => 'Курс B', 'en' => 'Course B'],
        ]);
        $package = PricingPackage::factory()->create([
            'course_id' => $course->id,
            'course_category_id' => $category->id,
            'name_translations' => ['ru' => 'Стандарт', 'en' => 'Standard'],
        ]);
        $branch = Branch::factory()->create([
            'name_translations' => ['ru' => 'Вильнюс', 'en' => 'Vilnius'],
        ]);
        $group = TrainingGroup::factory()->create([
            'branch_id' => $branch->id,
            'training_program_id' => $course->id,
            'course_category_id' => $category->id,
            'name_translations' => ['ru' => 'Вечерняя группа', 'en' => 'Evening group'],
        ]);
        $faq = Faq::factory()->create([
            'faqable_type' => $course->getMorphClass(),
            'faqable_id' => $course->id,
            'question_translations' => ['ru' => 'Есть теория?', 'en' => 'Is theory included?'],
        ]);
        $testimonial = Testimonial::factory()->create([
            'training_program_id' => $course->id,
            'branch_id' => $branch->id,
            'name_translations' => ['ru' => 'Иева', 'en' => 'Ieva'],
            'text_translations' => ['ru' => 'Отличный курс', 'en' => 'Great course'],
        ]);
        $setting = SiteSetting::factory()->create([
            'key' => 'default_currency',
            'value' => 'EUR',
            'is_public' => true,
        ]);

        $this->assertSame('Home', $page->getTranslation('title', 'en'));
        $this->assertSame('Category B', $category->displayName('en'));
        $this->assertSame('Course B', $course->displayTitle('en'));
        $this->assertSame('Standard', $package->displayName('en'));
        $this->assertSame('Vilnius', $branch->displayName('en'));
        $this->assertSame('Evening group', $group->displayName('en'));
        $this->assertSame('Is theory included?', $faq->displayQuestion('en'));
        $this->assertSame('Great course', $testimonial->displayText('en'));
        $this->assertSame('EUR', $setting->value);
    }

    public function test_visible_scopes_filter_public_catalog_records(): void
    {
        $category = CourseCategory::factory()->create(['is_active' => true, 'is_visible_on_site' => true]);
        CourseCategory::factory()->create(['is_active' => false, 'is_visible_on_site' => true]);
        $course = Course::factory()->create([
            'course_category_id' => $category->id,
            'is_active' => true,
            'is_visible_on_site' => true,
            'is_featured' => true,
        ]);
        Course::factory()->create([
            'course_category_id' => $category->id,
            'is_active' => true,
            'is_visible_on_site' => false,
        ]);
        Branch::factory()->create(['is_active' => true, 'is_visible_on_site' => true]);
        Branch::factory()->create(['is_active' => false, 'is_visible_on_site' => true]);
        PricingPackage::factory()->create([
            'course_id' => $course->id,
            'course_category_id' => $category->id,
            'is_active' => true,
            'is_visible_on_site' => true,
            'is_featured' => true,
        ]);
        PricingPackage::factory()->create([
            'course_id' => $course->id,
            'course_category_id' => $category->id,
            'is_active' => true,
            'is_visible_on_site' => false,
        ]);

        $this->assertSame(1, CourseCategory::query()->active()->visibleOnSite()->count());
        $this->assertSame(1, Course::query()->active()->visibleOnSite()->count());
        $this->assertSame(1, Course::query()->featured()->count());
        $this->assertSame(1, Branch::query()->active()->visibleOnSite()->count());
        $this->assertSame(1, PricingPackage::query()->active()->visibleOnSite()->count());
        $this->assertSame(1, PricingPackage::query()->featured()->count());
    }

    public function test_relationships_link_course_pricing_branch_group_and_lead(): void
    {
        $category = CourseCategory::factory()->create();
        $course = Course::factory()->create(['course_category_id' => $category->id]);
        $branch = Branch::factory()->create();
        $group = TrainingGroup::factory()->create([
            'branch_id' => $branch->id,
            'training_program_id' => $course->id,
            'course_category_id' => $category->id,
        ]);
        $package = PricingPackage::factory()->create([
            'course_id' => $course->id,
            'course_category_id' => $category->id,
        ]);
        $lead = Lead::factory()->create([
            'branch_id' => $branch->id,
            'training_program_id' => $course->id,
            'course_category_id' => $category->id,
            'training_group_id' => $group->id,
        ]);

        $this->assertTrue($category->courses()->whereKey($course->id)->exists());
        $this->assertTrue($course->pricingPackages()->whereKey($package->id)->exists());
        $this->assertTrue($branch->trainingGroups()->whereKey($group->id)->exists());
        $this->assertTrue($group->leads()->whereKey($lead->id)->exists());
        $this->assertTrue($lead->course->is($course));
        $this->assertTrue($lead->courseCategory->is($category));
        $this->assertTrue($lead->branch->is($branch));
        $this->assertTrue($lead->trainingGroup->is($group));
    }

    public function test_public_groups_only_show_visible_open_groups(): void
    {
        $visibleOpen = TrainingGroup::factory()->create([
            'status' => GroupStatus::Recruiting,
            'is_visible_on_site' => true,
            'capacity' => 12,
            'places_taken' => 4,
            'starts_on' => now()->addDays(10),
        ]);
        TrainingGroup::factory()->create([
            'status' => GroupStatus::Recruiting,
            'is_visible_on_site' => false,
            'capacity' => 12,
            'places_taken' => 4,
            'starts_on' => now()->addDays(10),
        ]);
        TrainingGroup::factory()->create([
            'status' => GroupStatus::Closed,
            'is_visible_on_site' => true,
            'capacity' => 12,
            'places_taken' => 4,
            'starts_on' => now()->addDays(10),
        ]);
        TrainingGroup::factory()->create([
            'status' => GroupStatus::Recruiting,
            'is_visible_on_site' => true,
            'capacity' => 12,
            'places_taken' => 12,
            'starts_on' => now()->addDays(10),
        ]);

        $groups = TrainingGroup::query()
            ->openForEnrollment()
            ->startsAfter(now())
            ->get();

        $this->assertCount(1, $groups);
        $this->assertTrue($groups->first()->is($visibleOpen));
        $this->assertSame(8, $visibleOpen->available_places);
        $this->assertFalse($visibleOpen->is_full);
    }
}
