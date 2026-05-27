<?php

namespace Tests\Feature;

use App\Enums\LeadStatus as LeadStatusEnum;
use App\Enums\LeadTaskPriority;
use App\Enums\LeadTaskStatus;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\LeadLostReason;
use App\Models\LeadSource;
use App\Models\LeadStatus;
use App\Models\LeadTag;
use App\Models\LeadTask;
use App\Models\TranslationString;
use App\Models\TranslationValue;
use Database\Seeders\CrmDemoLeadSeeder;
use Database\Seeders\CrmLostReasonSeeder;
use Database\Seeders\CrmSourceSeeder;
use Database\Seeders\CrmStatusSeeder;
use Database\Seeders\CrmTagSeeder;
use Database\Seeders\CrmTranslationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CrmFactoriesSeedersTest extends TestCase
{
    use RefreshDatabase;

    public function test_crm_factories_create_valid_records(): void
    {
        $status = LeadStatus::factory()->newStatus()->create();
        $source = LeadSource::factory()->website()->create();
        $lostReason = LeadLostReason::factory()->price()->create();
        $tag = LeadTag::factory()->hot()->create();
        $lead = Lead::factory()->fromWebsite()->withConsent()->withTags([$tag])->create([
            'status' => $status->code,
            'source' => $source->code,
            'lost_reason_code' => $lostReason->code,
        ]);
        $activity = LeadActivity::factory()->createdFromWebsite()->create(['marketing_lead_id' => $lead->id]);
        $task = LeadTask::factory()->open()->high()->assigned()->create(['marketing_lead_id' => $lead->id]);

        $this->assertDatabaseHas('lead_statuses', ['id' => $status->id, 'code' => 'new']);
        $this->assertDatabaseHas('lead_sources', ['id' => $source->id, 'code' => 'website']);
        $this->assertDatabaseHas('lead_lost_reasons', ['id' => $lostReason->id, 'code' => 'price']);
        $this->assertDatabaseHas('lead_tags', ['id' => $tag->id, 'slug' => 'hot']);
        $this->assertDatabaseHas('lead_tag_marketing_lead', [
            'marketing_lead_id' => $lead->id,
            'lead_tag_id' => $tag->id,
        ]);
        $this->assertSame('created_from_website', $activity->type);
        $this->assertSame(LeadTaskStatus::Open, $task->status);
        $this->assertSame(LeadTaskPriority::High, $task->priority);
        $this->assertFalse(Schema::hasTable('lead_call_logs'));
    }

    public function test_lead_factory_from_website_creates_valid_website_lead(): void
    {
        $lead = Lead::factory()->fromWebsite()->withConsent()->create();

        $this->assertSame('website', $lead->source);
        $this->assertSame('enrollment', $lead->form_name);
        $this->assertSame(LeadStatusEnum::New, $lead->status);
        $this->assertTrue($lead->consent_accepted);
    }

    public function test_lead_factory_with_utm_saves_utm_fields(): void
    {
        $lead = Lead::factory()->withUtm()->create();

        $this->assertSame('google', $lead->utm_source);
        $this->assertSame('cpc', $lead->utm_medium);
        $this->assertSame('public-website-demo', $lead->utm_campaign);
        $this->assertSame('lead-form', $lead->utm_content);
        $this->assertSame('driving school', $lead->utm_term);
        $this->assertSame('https://drivepro.test/?utm_source=google', $lead->landing_page);
        $this->assertSame('https://drivepro.test/apply', $lead->form_page);
    }

    public function test_lead_factory_overdue_creates_overdue_follow_up(): void
    {
        $lead = Lead::factory()->overdue()->create();

        $this->assertTrue($lead->next_follow_up_at->isPast());
        $this->assertTrue(Lead::query()->overdueFollowUp()->whereKey($lead->id)->exists());
    }

    public function test_crm_status_seeder_is_idempotent_and_default_status_exists(): void
    {
        $this->seed(CrmStatusSeeder::class);
        $firstCount = LeadStatus::query()->count();

        $this->seed(CrmStatusSeeder::class);

        $this->assertSame($firstCount, LeadStatus::query()->count());
        $this->assertSame(1, LeadStatus::query()->where('is_default', true)->count());
        $this->assertDatabaseHas('lead_statuses', ['code' => 'new', 'is_default' => true]);
        $this->assertDatabaseHas('lead_statuses', ['code' => 'duplicate', 'is_duplicate' => true]);
        $this->assertDatabaseHas('lead_statuses', ['code' => 'spam', 'is_spam' => true]);
    }

    public function test_crm_source_seeder_is_idempotent(): void
    {
        $this->seed(CrmSourceSeeder::class);
        $firstCount = LeadSource::query()->count();

        $this->seed(CrmSourceSeeder::class);

        $this->assertSame($firstCount, LeadSource::query()->count());
        $this->assertDatabaseHas('lead_sources', ['code' => 'website']);
        $this->assertDatabaseHas('lead_sources', ['code' => 'contact_form']);
    }

    public function test_crm_lost_reason_seeder_is_idempotent(): void
    {
        $this->seed(CrmLostReasonSeeder::class);
        $firstCount = LeadLostReason::query()->count();

        $this->seed(CrmLostReasonSeeder::class);

        $this->assertSame($firstCount, LeadLostReason::query()->count());
        $this->assertDatabaseHas('lead_lost_reasons', ['code' => 'price']);
        $this->assertDatabaseHas('lead_lost_reasons', ['code' => 'other']);
    }

    public function test_crm_tag_seeder_is_idempotent(): void
    {
        $this->seed(CrmTagSeeder::class);
        $firstCount = LeadTag::query()->count();

        $this->seed(CrmTagSeeder::class);

        $this->assertSame($firstCount, LeadTag::query()->count());
        $this->assertDatabaseHas('lead_tags', ['slug' => 'hot']);
        $this->assertDatabaseHas('lead_tags', ['slug' => 'corporate_client']);
    }

    public function test_crm_translation_seeder_creates_translation_keys(): void
    {
        $this->seed(CrmTranslationSeeder::class);

        $translation = TranslationString::query()
            ->where('key', 'crm.leads.title')
            ->firstOrFail();

        $this->assertDatabaseHas('translation_strings', ['key' => 'crm.leads.title']);
        $this->assertSame(
            ['en', 'lt', 'pl', 'ru'],
            TranslationValue::query()
                ->where('translation_string_id', $translation->id)
                ->pluck('language_code')
                ->sort()
                ->values()
                ->all(),
        );
    }

    public function test_crm_demo_lead_seeder_creates_leads_through_factories_without_duplicates(): void
    {
        $this->seed(CrmDemoLeadSeeder::class);
        $firstLeadCount = Lead::query()->count();

        $this->seed(CrmDemoLeadSeeder::class);

        $this->assertSame($firstLeadCount, Lead::query()->count());
        $this->assertDatabaseHas('marketing_leads', [
            'email' => 'crm-demo-new-website@drivepro.test',
            'source' => 'website',
            'status' => 'new',
        ]);
        $this->assertDatabaseHas('marketing_leads', [
            'email' => 'crm-demo-callback@drivepro.test',
            'source' => 'callback',
        ]);
        $this->assertDatabaseHas('marketing_leads', [
            'email' => 'crm-demo-overdue@drivepro.test',
        ]);
        $this->assertTrue(Lead::query()->overdueFollowUp()->where('email', 'crm-demo-overdue@drivepro.test')->exists());
        $this->assertTrue(LeadTask::query()->where('title', 'CRM demo overdue follow-up')->exists());
        $this->assertTrue(LeadActivity::query()->where('type', 'created_from_website')->exists());
        $this->assertDatabaseHas('lead_tag_marketing_lead', [
            'marketing_lead_id' => Lead::query()->where('email', 'crm-demo-utm@drivepro.test')->value('id'),
            'lead_tag_id' => LeadTag::query()->where('slug', 'hot')->value('id'),
        ]);
    }
}
