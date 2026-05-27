<?php

namespace Tests\Feature;

use App\Actions\CaptureUtmDataAction;
use App\Actions\CreateCallbackLeadAction;
use App\Actions\CreateWebsiteLeadAction;
use App\Actions\NormalizePhoneAction;
use App\Actions\PublishBranchOnSiteAction;
use App\Actions\PublishCourseOnSiteAction;
use App\Actions\ResolveWebsiteCourseContextAction;
use App\Actions\StoreUtmInSessionAction;
use App\Enums\GroupStatus;
use App\Enums\LeadTaskPriority;
use App\Enums\LeadTaskStatus;
use App\Models\Branch;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\Lead;
use App\Models\TrainingGroup;
use App\Models\User;
use App\Notifications\EnrollmentLeadAutoReplyNotification;
use App\Notifications\EnrollmentLeadSubmittedNotification;
use App\Rules\ConsentAcceptedRule;
use App\Rules\PhoneOrEmailRequiredRule;
use App\Rules\PublicPageIndexableRule;
use App\Rules\PublicBranchCanBePublishedRule;
use App\Rules\PublicCourseCanBePublishedRule;
use App\Rules\PublishedPageRequirementRule;
use App\Rules\SeoMetadataRule;
use App\Rules\TranslatedFieldRequiredRule;
use App\Rules\ValidLocaleRule;
use App\Rules\ValidCanonicalUrlRule;
use App\Rules\ValidPriceRule;
use App\Rules\ValidPublicBranchRule;
use App\Rules\ValidPublicCourseRule;
use App\Rules\ValidPublicTrainingGroupRule;
use App\Rules\ValidSlugRule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class PublicWebsiteActionsRequestsRulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_website_lead_creation_action_creates_crm_lead_with_context_and_tracking(): void
    {
        Notification::fake();
        $this->seed();
        $category = CourseCategory::factory()->create();
        $course = Course::factory()->create([
            'course_category_id' => $category->id,
            'license_category' => 'B',
            'is_active' => true,
            'is_visible_on_site' => true,
        ]);
        $branch = Branch::factory()->create(['is_active' => true, 'is_visible_on_site' => true]);
        $group = TrainingGroup::factory()->create([
            'training_program_id' => $course->id,
            'course_category_id' => $category->id,
            'branch_id' => $branch->id,
            'status' => GroupStatus::Recruiting,
            'is_visible_on_site' => true,
            'capacity' => 12,
            'places_taken' => 3,
        ]);

        $lead = app(CreateWebsiteLeadAction::class)->handle([
            'training_program_id' => $course->id,
            'branch_id' => $branch->id,
            'training_group_id' => $group->id,
            'first_name' => 'Ieva',
            'last_name' => 'Norkute',
            'email' => 'ieva.actions@example.com',
            'phone' => '+370 600 11111',
            'preferred_format' => 'mixed',
            'preferred_language' => 'en',
            'privacy_consent' => '1',
            'utm_source' => 'google',
            'utm_campaign' => 'actions',
        ]);

        $this->assertInstanceOf(Lead::class, $lead);
        $this->assertSame('website', $lead->source);
        $this->assertSame($course->id, $lead->training_program_id);
        $this->assertSame($category->id, $lead->course_category_id);
        $this->assertSame($branch->id, $lead->branch_id);
        $this->assertSame($group->id, $lead->training_group_id);
        $this->assertSame('+37060011111', $lead->phone);
        $this->assertSame('+37060011111', $lead->normalized_phone);
        $this->assertSame('actions', $lead->utm_campaign);
        $this->assertSame(1, $lead->tasks()->count());
        $task = $lead->tasks()->firstOrFail();
        $this->assertSame(tkey('crm.tasks.defaults.contact_new_website_lead'), $task->title);
        $this->assertSame(LeadTaskPriority::High, $task->priority);
        $this->assertSame(LeadTaskStatus::Open, $task->status);
        Notification::assertSentOnDemand(EnrollmentLeadAutoReplyNotification::class);
    }

    public function test_callback_lead_creation_action_creates_follow_up_records(): void
    {
        Notification::fake();
        $this->seed();
        $admin = User::query()
            ->where('email', 'admin@example.com')
            ->firstOrFail();
        $category = CourseCategory::factory()->create();
        $course = Course::factory()->create([
            'course_category_id' => $category->id,
            'is_active' => true,
            'is_visible_on_site' => true,
        ]);
        $branch = Branch::factory()->create(['is_active' => true, 'is_visible_on_site' => true]);

        $lead = app(CreateCallbackLeadAction::class)->handle([
            'first_name' => 'Tomas',
            'phone' => '+370 600 22222',
            'training_program_id' => $course->id,
            'branch_id' => $branch->id,
            'preferred_time' => 'Tomorrow morning',
            'privacy_consent' => '1',
            'utm_campaign' => 'callback-actions',
        ]);

        $this->assertSame('callback', $lead->source);
        $this->assertSame('callback', $lead->form_name);
        $this->assertSame($category->id, $lead->course_category_id);
        $this->assertSame('+37060022222', $lead->normalized_phone);
        $this->assertSame('Tomorrow morning', $lead->preferred_time);
        $this->assertSame(1, $lead->comments()->count());
        $this->assertSame(1, $lead->communications()->count());
        $this->assertSame(1, $lead->tasks()->count());
        $this->assertSame(LeadTaskPriority::High, $lead->tasks()->firstOrFail()->priority);
        Notification::assertSentTo($admin, EnrollmentLeadSubmittedNotification::class);
    }

    public function test_utm_capture_action_reads_query_session_and_hidden_fields(): void
    {
        $session = app('session.store');
        $landing = Request::create('/?utm_source=google&utm_campaign=first-touch', 'GET');
        $landing->setLaravelSession($session);
        $landing->headers->set('referer', 'https://example.com/referrer');

        app(StoreUtmInSessionAction::class)->handle($landing);

        $secondLanding = Request::create('/prices?utm_source=bing&utm_campaign=second-touch', 'GET');
        $secondLanding->setLaravelSession($session);

        $tracking = app(StoreUtmInSessionAction::class)->handle($secondLanding);

        $form = Request::create('/apply?utm_medium=cpc', 'POST', [
            'utm_campaign' => 'hidden-campaign',
            'form_name' => 'enrollment',
        ]);
        $form->setLaravelSession($session);

        $payload = app(CaptureUtmDataAction::class)->handle($form);

        $this->assertSame('google', $tracking['utm_source']);
        $this->assertSame('first-touch', $tracking['utm_campaign']);
        $this->assertStringContainsString('/prices?', (string) $tracking['current_page']);
        $this->assertStringContainsString('utm_source=bing', (string) $tracking['current_page']);
        $this->assertStringContainsString('utm_campaign=second-touch', (string) $tracking['current_page']);
        $this->assertSame('google', $payload['utm_source']);
        $this->assertSame('cpc', $payload['utm_medium']);
        $this->assertSame('hidden-campaign', $payload['utm_campaign']);
        $this->assertSame('https://example.com/referrer', $payload['referrer_url']);
        $this->assertStringContainsString('utm_source=google', (string) $payload['landing_page']);
        $this->assertSame('enrollment', $payload['form_name']);
    }

    public function test_phone_normalization(): void
    {
        $normalize = app(NormalizePhoneAction::class);

        $this->assertNull($normalize->handle(null));
        $this->assertSame('+37060011111', $normalize->handle('+370 600-11.111'));
        $this->assertSame('+37060011111', $normalize->handle('0037060011111'));
        $this->assertSame('+37060011111', $normalize->handle('860011111'));
    }

    public function test_course_context_resolution_infers_course_category_and_branch_from_group(): void
    {
        $category = CourseCategory::factory()->create();
        $course = Course::factory()->create([
            'course_category_id' => $category->id,
            'is_active' => true,
            'is_visible_on_site' => true,
        ]);
        $branch = Branch::factory()->create(['is_active' => true, 'is_visible_on_site' => true]);
        $group = TrainingGroup::factory()->create([
            'training_program_id' => $course->id,
            'branch_id' => $branch->id,
            'status' => GroupStatus::Recruiting,
            'is_visible_on_site' => true,
            'capacity' => 10,
            'places_taken' => 2,
        ]);

        $context = app(ResolveWebsiteCourseContextAction::class)->handle([
            'training_group_id' => $group->id,
        ]);

        $this->assertSame($course->id, $context['course_id']);
        $this->assertSame($category->id, $context['course_category_id']);
        $this->assertSame($branch->id, $context['branch_id']);
        $this->assertSame($group->id, $context['training_group_id']);
    }

    public function test_invalid_public_course_rule_rejects_hidden_course(): void
    {
        $this->seed();
        $course = Course::factory()->create([
            'is_active' => true,
            'is_visible_on_site' => false,
        ]);

        $validator = Validator::make(
            ['course_id' => $course->id],
            ['course_id' => [new ValidPublicCourseRule]],
        );

        $this->assertTrue($validator->fails());
        $this->assertSame(tkey('website.validation.invalid_public_course'), $validator->errors()->first('course_id'));
    }

    public function test_invalid_public_training_group_rule_rejects_full_group(): void
    {
        $this->seed();
        $group = TrainingGroup::factory()->create([
            'status' => GroupStatus::Recruiting,
            'is_visible_on_site' => true,
            'capacity' => 8,
            'places_taken' => 8,
        ]);

        $validator = Validator::make(
            ['training_group_id' => $group->id],
            ['training_group_id' => [new ValidPublicTrainingGroupRule]],
        );

        $this->assertTrue($validator->fails());
        $this->assertSame(tkey('website.validation.group_is_full'), $validator->errors()->first('training_group_id'));
    }

    public function test_consent_and_translated_field_rules_use_translation_messages(): void
    {
        $this->seed();

        $consent = Validator::make(
            ['privacy_consent' => '0'],
            ['privacy_consent' => [new ConsentAcceptedRule]],
        );
        $translation = Validator::make(
            ['name_translations' => ['en' => 'Category B']],
            ['name_translations' => [new TranslatedFieldRequiredRule]],
        );

        $this->assertTrue($consent->fails());
        $this->assertTrue($translation->fails());
        $this->assertSame(tkey('website.validation.consent_required'), $consent->errors()->first('privacy_consent'));
        $this->assertSame(tkey('website.validation.default_translation_required'), $translation->errors()->first('name_translations'));
        $this->assertNotSame('website.validation.consent_required', tkey('website.validation.consent_required', [], 'en'));
    }

    public function test_public_website_validation_rule_messages_are_translated(): void
    {
        $this->seed();
        $hiddenBranch = Branch::factory()->create([
            'is_active' => true,
            'is_visible_on_site' => false,
        ]);
        $unpublishableCourse = Course::factory()->create([
            'slug' => '',
            'name_translations' => [],
            'title_translations' => [],
            'description_translations' => [],
            'price_cents' => 0,
            'is_active' => true,
            'is_visible_on_site' => false,
        ]);
        $unpublishableBranch = Branch::factory()->create([
            'slug' => '',
            'name_translations' => [],
            'phone' => null,
            'email' => null,
            'is_active' => true,
            'is_visible_on_site' => false,
        ]);

        $cases = [
            ['contact', ['contact' => null], ['contact' => [new PhoneOrEmailRequiredRule]], 'website.validation.phone_or_email_required'],
            ['branch_id', ['branch_id' => $hiddenBranch->id], ['branch_id' => [new ValidPublicBranchRule]], 'website.validation.invalid_public_branch'],
            ['slug', ['slug' => 'Invalid Slug'], ['slug' => [new ValidSlugRule]], 'website.validation.invalid_slug'],
            ['canonical_url', ['canonical_url' => 'ftp://example.test/page'], ['canonical_url' => [new ValidCanonicalUrlRule]], 'website.validation.invalid_canonical_url'],
            ['is_indexable', ['is_indexable' => '1', 'is_active' => '0'], ['is_indexable' => [new PublicPageIndexableRule]], 'website.validation.public_page_not_indexable'],
            ['price', ['price' => '-1'], ['price' => [new ValidPriceRule]], 'website.validation.invalid_price'],
            ['locale', ['locale' => 'zz'], ['locale' => [new ValidLocaleRule]], 'website.validation.invalid_locale'],
            ['seo_title', ['seo_title' => str_repeat('A', 71)], ['seo_title' => [new SeoMetadataRule(70)]], 'website.validation.seo_title_too_long'],
            [
                'publish',
                ['publish' => true, 'slug' => '', 'title_translations' => [], 'content_translations' => []],
                ['publish' => [new PublishedPageRequirementRule]],
                'website.validation.page_cannot_be_published',
            ],
            ['course', ['course' => $unpublishableCourse->id], ['course' => [new PublicCourseCanBePublishedRule]], 'website.validation.course_cannot_be_published'],
            ['branch', ['branch' => $unpublishableBranch->id], ['branch' => [new PublicBranchCanBePublishedRule]], 'website.validation.branch_cannot_be_published'],
        ];

        foreach ($cases as [$field, $data, $rules, $key]) {
            $validator = Validator::make($data, $rules);

            $this->assertTrue($validator->fails(), $key);
            $this->assertSame(tkey($key), $validator->errors()->first($field));
            $this->assertNotSame($key, tkey($key, [], 'en'));
        }
    }

    public function test_publish_course_action_validates_and_makes_course_visible(): void
    {
        $this->seed();
        $course = Course::factory()->create([
            'slug' => 'publishable-course',
            'name_translations' => ['ru' => 'Курс B', 'en' => 'Course B'],
            'title_translations' => ['ru' => 'Курс B', 'en' => 'Course B'],
            'description_translations' => ['ru' => 'Описание', 'en' => 'Description'],
            'price_cents' => 120000,
            'is_active' => true,
            'is_visible_on_site' => false,
        ]);

        $published = app(PublishCourseOnSiteAction::class)->handle($course);

        $this->assertTrue($published->is_visible_on_site);
    }

    public function test_publish_branch_action_validates_and_makes_branch_visible(): void
    {
        $this->seed();
        $branch = Branch::factory()->create([
            'slug' => 'publishable-branch',
            'name_translations' => ['ru' => 'Вильнюс', 'en' => 'Vilnius'],
            'phone' => '+370 600 33333',
            'is_active' => true,
            'is_visible_on_site' => false,
        ]);

        $published = app(PublishBranchOnSiteAction::class)->handle($branch);

        $this->assertTrue($published->is_visible_on_site);
    }
}
