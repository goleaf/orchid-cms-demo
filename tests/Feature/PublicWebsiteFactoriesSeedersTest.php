<?php

namespace Tests\Feature;

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
use App\Models\TranslationString;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\WebsiteBranchSeeder;
use Database\Seeders\WebsiteCourseSeeder;
use Database\Seeders\WebsiteFaqSeeder;
use Database\Seeders\WebsitePageSeeder;
use Database\Seeders\WebsitePricingSeeder;
use Database\Seeders\WebsiteSettingsSeeder;
use Database\Seeders\WebsiteTestimonialSeeder;
use Database\Seeders\WebsiteTranslationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicWebsiteFactoriesSeedersTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_website_factories_create_valid_records(): void
    {
        $page = SitePage::factory()->home()->published()->indexable()->translated()->create(['slug' => 'factory-home']);
        $category = CourseCategory::factory()->translated()->categoryB()->active()->visibleOnSite()->create();
        $course = Course::factory()
            ->active()
            ->visibleOnSite()
            ->featured()
            ->translated()
            ->withPrice()
            ->withOldPrice()
            ->hybrid()
            ->create(['course_category_id' => $category->id, 'slug' => 'factory-course']);
        $package = PricingPackage::factory()
            ->premium()
            ->active()
            ->visibleOnSite()
            ->featured()
            ->translated()
            ->create([
                'course_id' => $course->id,
                'course_category_id' => $category->id,
                'slug' => 'factory-premium',
            ]);
        $branch = Branch::factory()
            ->active()
            ->visibleOnSite()
            ->translated()
            ->withCoordinates()
            ->withContacts()
            ->create(['slug' => 'factory-branch']);
        $group = TrainingGroup::factory()
            ->translated()
            ->recruiting()
            ->visibleOnSite()
            ->startingSoon()
            ->evening()
            ->withCapacity(10, 2)
            ->create([
                'training_program_id' => $course->id,
                'course_category_id' => $category->id,
                'branch_id' => $branch->id,
            ]);
        $faq = Faq::factory()->forCourse($course)->active()->translated()->create();
        $testimonial = Testimonial::factory()
            ->active()
            ->featured()
            ->published()
            ->withRating(5)
            ->withVideo()
            ->translated()
            ->create([
                'training_program_id' => $course->id,
                'branch_id' => $branch->id,
            ]);
        $setting = SiteSetting::factory()->public()->groupWebsite()->create();
        $lead = Lead::factory()
            ->fromWebsite()
            ->withUtm()
            ->withConsent()
            ->forTrainingGroup($group)
            ->create();

        $this->assertTrue($page->is_active);
        $this->assertSame('Категория B', $category->displayName('ru'));
        $this->assertTrue($course->is_featured);
        $this->assertTrue($package->is_visible_on_site);
        $this->assertNotNull($branch->latitude);
        $this->assertSame(8, $group->available_places);
        $this->assertTrue($faq->faqable->is($course));
        $this->assertTrue($testimonial->is_featured);
        $this->assertTrue($setting->is_public);
        $this->assertSame('public-website-demo', $lead->utm_campaign);
        $this->assertTrue($lead->consent_accepted);
        $this->assertTrue($lead->trainingGroup->is($group));
    }

    public function test_website_page_seeder_is_idempotent(): void
    {
        $this->seed(WebsitePageSeeder::class);
        $this->seed(WebsitePageSeeder::class);

        $this->assertSame(6, SitePage::query()->count());
        $this->assertTrue(SitePage::query()->where('type', 'home')->where('slug', 'home')->exists());
    }

    public function test_website_course_seeder_creates_visible_courses(): void
    {
        $this->seed(WebsiteCourseSeeder::class);

        $this->assertGreaterThanOrEqual(5, CourseCategory::query()->active()->visibleOnSite()->count());
        $this->assertGreaterThanOrEqual(5, Course::query()->active()->visibleOnSite()->count());
        $this->assertTrue(Course::query()->where('slug', 'category-b-manual')->visibleOnSite()->exists());
    }

    public function test_website_pricing_seeder_creates_pricing_packages(): void
    {
        $this->seed(WebsitePricingSeeder::class);

        $this->assertSame(4, PricingPackage::query()->active()->visibleOnSite()->count());
        $this->assertTrue(PricingPackage::query()->where('slug', 'category-b-premium')->featured()->exists());
    }

    public function test_website_branch_seeder_creates_active_branches(): void
    {
        $this->seed(WebsiteBranchSeeder::class);

        $this->assertGreaterThanOrEqual(2, Branch::query()->active()->visibleOnSite()->count());
        $this->assertTrue(Branch::query()->where('slug', 'vilnius-main')->active()->visibleOnSite()->exists());
    }

    public function test_website_faq_seeder_creates_common_faq_idempotently(): void
    {
        $this->seed(WebsiteFaqSeeder::class);
        $this->seed(WebsiteFaqSeeder::class);

        $faq = Faq::query()->ordered()->firstOrFail();

        $this->assertSame(3, Faq::query()->active()->count());
        $this->assertArrayHasKey('en', $faq->question_translations);
        $this->assertArrayHasKey('ru', $faq->answer_translations);
    }

    public function test_website_testimonial_seeder_creates_published_testimonials_idempotently(): void
    {
        $this->seed(WebsiteTestimonialSeeder::class);
        $this->seed(WebsiteTestimonialSeeder::class);

        $testimonial = Testimonial::query()->published()->featured()->firstOrFail();

        $this->assertSame(2, Testimonial::query()->published()->count());
        $this->assertSame(5, $testimonial->rating);
        $this->assertArrayHasKey('lt', $testimonial->text_translations);
    }

    public function test_website_settings_seeder_creates_default_settings_idempotently(): void
    {
        $this->seed(WebsiteSettingsSeeder::class);
        $this->seed(WebsiteSettingsSeeder::class);

        $this->assertSame(9, SiteSetting::query()->count());
        $this->assertDatabaseHas('site_settings', [
            'key' => 'default_phone',
            'group' => 'contacts',
            'is_public' => true,
        ]);
        $this->assertDatabaseHas('site_settings', [
            'key' => 'robots_txt',
            'group' => 'seo',
            'is_public' => false,
        ]);
    }

    public function test_website_translation_seeder_creates_translation_keys(): void
    {
        $this->seed(LanguageSeeder::class);
        $this->seed(WebsiteTranslationSeeder::class);

        $translation = TranslationString::query()
            ->with('values')
            ->where('key', 'website.nav.home')
            ->firstOrFail();

        $this->assertSame('website', $translation->group);
        $this->assertEqualsCanonicalizing(['en', 'lt', 'pl', 'ru'], $translation->values->pluck('language_code')->all());
        $this->assertNotSame('website.nav.home', tkey('website.nav.home', [], 'en'));
    }

    public function test_lead_factory_can_create_website_lead_with_utm_and_consent(): void
    {
        $lead = Lead::factory()
            ->fromWebsite()
            ->withUtm()
            ->withConsent()
            ->create();

        $this->assertSame('website', $lead->source);
        $this->assertSame('enrollment', $lead->form_name);
        $this->assertSame('google', $lead->utm_source);
        $this->assertTrue($lead->consent_accepted);
        $this->assertNotNull($lead->consent_accepted_at);
    }
}
