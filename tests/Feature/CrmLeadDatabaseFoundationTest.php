<?php

namespace Tests\Feature;

use App\Enums\LeadStatus as LeadStatusEnum;
use App\Enums\LeadTaskStatus;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\LeadLostReason;
use App\Models\LeadSource;
use App\Models\LeadStatus;
use App\Models\LeadTag;
use App\Models\LeadTask;
use App\Models\MarketingLeadCommunication;
use App\Models\User;
use Database\Seeders\CrmDictionarySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CrmLeadDatabaseFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_required_crm_tables_are_mapped_to_existing_lead_storage(): void
    {
        foreach ([
            'lead_statuses',
            'lead_sources',
            'lead_lost_reasons',
            'lead_tags',
            'lead_tag_marketing_lead',
            'marketing_leads',
            'marketing_lead_activities',
            'marketing_lead_tasks',
            'marketing_lead_communications',
        ] as $table) {
            $this->assertTrue(Schema::hasTable($table), $table);
        }

        $this->assertFalse(Schema::hasTable('leads'));
        $this->assertFalse(Schema::hasTable('website_leads'));
    }

    public function test_crm_foundation_columns_and_seeded_dictionaries_exist(): void
    {
        $this->seed(CrmDictionarySeeder::class);

        foreach ([
            'uuid',
            'lead_number',
            'full_name',
            'first_name',
            'last_name',
            'middle_name',
            'phone',
            'normalized_phone',
            'email',
            'messenger',
            'city',
            'locale',
            'message',
            'internal_comment',
            'status',
            'source',
            'responsible_manager_id',
            'lost_reason_code',
            'duplicate_of_id',
            'training_program_id',
            'course_category_id',
            'branch_id',
            'training_group_id',
            'desired_start_date',
            'preferred_time',
            'preferred_language',
            'preferred_gearbox',
            'budget_cents',
            'priority',
            'lead_score',
            'last_contacted_at',
            'next_follow_up_at',
            'closed_at',
            'converted_at',
            'converted_student_profile_id',
            'converted_enrollment_id',
            'utm_source',
            'utm_medium',
            'utm_campaign',
            'utm_content',
            'utm_term',
            'referrer_url',
            'landing_page',
            'form_page',
            'form_name',
            'ip_address',
            'user_agent',
            'consent_accepted',
            'consent_accepted_at',
            'consent_text_version',
            'created_by_user_id',
            'updated_by_user_id',
            'deleted_at',
        ] as $column) {
            $this->assertTrue(Schema::hasColumn('marketing_leads', $column), $column);
        }

        foreach (['lead_statuses', 'lead_sources', 'lead_lost_reasons', 'lead_tags'] as $table) {
            $this->assertTrue(Schema::hasColumn($table, 'description_translations'), $table);
        }

        foreach (['is_public', 'is_duplicate', 'is_spam'] as $column) {
            $this->assertTrue(Schema::hasColumn('lead_statuses', $column), $column);
        }

        foreach (['title_translations', 'description_translations', 'cancelled_at'] as $column) {
            $this->assertTrue(Schema::hasColumn('marketing_lead_tasks', $column), $column);
        }

        foreach (['call_result', 'duration_seconds'] as $column) {
            $this->assertTrue(Schema::hasColumn('marketing_lead_communications', $column), $column);
        }

        $this->assertSame(1, LeadStatus::query()->where('is_default', true)->count());
        $this->assertDatabaseHas('lead_statuses', ['code' => 'new', 'is_default' => true]);
        $this->assertDatabaseHas('lead_statuses', ['code' => 'duplicate', 'is_duplicate' => true]);
        $this->assertDatabaseHas('lead_statuses', ['code' => 'spam', 'is_spam' => true]);

        foreach ([
            'new',
            'no_answer',
            'contacted',
            'consultation',
            'waiting_documents',
            'waiting_payment',
            'ready_to_enroll',
            'enrolled',
            'lost',
            'duplicate',
            'spam',
            'archived',
        ] as $status) {
            $this->assertDatabaseHas('lead_statuses', ['code' => $status]);
        }

        foreach ([
            'website',
            'callback',
            'contact_form',
            'phone',
            'office',
            'google_ads',
            'facebook',
            'instagram',
            'tiktok',
            'telegram',
            'whatsapp',
            'referral',
            'partner',
            'other',
        ] as $source) {
            $this->assertDatabaseHas('lead_sources', ['code' => $source]);
        }

        foreach ([
            'price',
            'schedule',
            'location',
            'competitor',
            'no_response',
            'changed_mind',
            'documents',
            'payment',
            'language',
            'car_type',
            'duplicate',
            'spam',
            'other',
        ] as $reason) {
            $this->assertDatabaseHas('lead_lost_reasons', ['code' => $reason]);
        }

        foreach ([
            'hot',
            'vip',
            'needs_call',
            'ready_to_pay',
            'needs_documents',
            'repeat_request',
            'problematic',
            'thinking',
            'urgent',
            'individual_schedule',
            'wants_automatic',
            'wants_manual',
            'evening_training',
            'weekend_training',
            'corporate_client',
        ] as $tag) {
            $this->assertDatabaseHas('lead_tags', ['slug' => $tag]);
        }
    }

    public function test_lead_relationships_helpers_and_translated_dictionary_names_work(): void
    {
        app()->setLocale('en');

        $manager = User::factory()->create();
        $status = LeadStatus::factory()->create([
            'code' => 'new',
            'name_translations' => ['ru' => 'Новая', 'en' => 'New CRM lead'],
        ]);
        $source = LeadSource::factory()->create([
            'code' => 'website',
            'name_translations' => ['ru' => 'Сайт', 'en' => 'Website CRM source'],
        ]);
        $lostReason = LeadLostReason::factory()->create(['code' => 'price']);
        $tag = LeadTag::factory()->create(['slug' => 'urgent']);
        $lead = Lead::factory()->create([
            'lead_number' => 'CRM-FOUNDATION-1',
            'responsible_manager_id' => $manager->id,
            'status' => 'new',
            'source' => 'website',
            'lost_reason_code' => 'price',
            'first_name' => 'Ada',
            'middle_name' => 'CRM',
            'last_name' => 'Lovelace',
            'phone' => '+370 600 11111',
            'email' => 'ada@example.com',
            'message' => 'Searchable public comment',
            'internal_comment' => 'Searchable internal comment',
        ]);

        $lead->tags()->attach($tag);
        LeadActivity::factory()->create([
            'marketing_lead_id' => $lead->id,
            'user_id' => $manager->id,
            'type' => 'created',
        ]);
        LeadTask::factory()->create([
            'marketing_lead_id' => $lead->id,
            'assigned_to_user_id' => $manager->id,
            'created_by_user_id' => $manager->id,
            'title' => 'Call lead',
            'title_translations' => ['en' => 'Call lead translation'],
        ]);

        $duplicate = Lead::factory()->create([
            'status' => LeadStatusEnum::Duplicate,
            'duplicate_of_id' => $lead->id,
        ]);

        $this->assertTrue($lead->status()->first()->is($status));
        $this->assertTrue($lead->source()->first()->is($source));
        $this->assertTrue($lead->manager->is($manager));
        $this->assertTrue($lead->lostReason->is($lostReason));
        $this->assertTrue($lead->tags->first()->is($tag));
        $this->assertInstanceOf(LeadActivity::class, $lead->activities()->first());
        $this->assertInstanceOf(LeadTask::class, $lead->tasks()->first());
        $this->assertTrue($lead->activities()->firstOrFail()->lead->is($lead));
        $this->assertTrue($lead->activities()->firstOrFail()->user->is($manager));
        $this->assertTrue($lead->tasks()->firstOrFail()->lead->is($lead));
        $this->assertTrue($lead->tasks()->firstOrFail()->assignedTo->is($manager));
        $this->assertTrue($lead->tasks()->firstOrFail()->createdBy->is($manager));
        $this->assertTrue($duplicate->duplicateOf->is($lead));
        $this->assertTrue($lead->duplicates->first()->is($duplicate));
        $this->assertSame('New CRM lead', $status->display_name);
        $this->assertSame('Website CRM source', $source->display_name);
        $this->assertSame('Ada CRM Lovelace', $lead->display_name);
        $this->assertSame('+370 600 11111 / ada@example.com', $lead->display_contact);
        $this->assertTrue($duplicate->is_duplicate);
        $this->assertSame('Call lead translation', $lead->tasks()->firstOrFail()->display_title);
    }

    public function test_call_logs_are_stored_as_phone_communications(): void
    {
        $lead = Lead::factory()->create();
        $user = User::factory()->create();

        $phoneLog = MarketingLeadCommunication::factory()->create([
            'marketing_lead_id' => $lead->id,
            'user_id' => $user->id,
            'channel' => 'phone',
            'call_result' => 'reached',
            'duration_seconds' => 180,
        ]);
        $emailLog = MarketingLeadCommunication::factory()->create([
            'marketing_lead_id' => $lead->id,
            'user_id' => $user->id,
            'channel' => 'email',
        ]);

        $this->assertTrue($lead->callLogs()->whereKey($phoneLog->id)->exists());
        $this->assertFalse($lead->callLogs()->whereKey($emailLog->id)->exists());
        $this->assertTrue($phoneLog->lead->is($lead));
        $this->assertTrue($phoneLog->user->is($user));
    }

    public function test_lead_search_and_pipeline_scopes_work(): void
    {
        $this->travelTo(now()->startOfDay()->addHours(10));

        $lead = Lead::factory()->create([
            'lead_number' => 'CRM-SEARCH-1',
            'uuid' => '11111111-1111-4111-8111-111111111111',
            'full_name' => 'Foundation Search',
            'first_name' => 'Foundation',
            'last_name' => 'Needle',
            'phone' => '+370 600 22222',
            'email' => 'foundation@example.com',
            'message' => 'public comment needle',
            'internal_comment' => 'internal comment needle',
            'status' => LeadStatusEnum::New,
            'next_follow_up_at' => now()->subHour(),
        ]);
        $dueToday = Lead::factory()->create([
            'status' => LeadStatusEnum::Contacted,
            'next_follow_up_at' => now()->addHours(2),
        ]);
        $closed = Lead::factory()->create([
            'status' => LeadStatusEnum::Lost,
            'closed_at' => now(),
        ]);
        $converted = Lead::factory()->create([
            'status' => LeadStatusEnum::Enrolled,
            'converted_at' => now(),
        ]);

        foreach ([
            (string) $lead->id,
            '11111111-1111-4111-8111-111111111111',
            'CRM-SEARCH-1',
            'Foundation Search',
            'Foundation',
            'Needle',
            '+370 600 22222',
            '37060022222',
            'foundation@example.com',
            'public comment needle',
            'internal comment needle',
        ] as $token) {
            $this->assertTrue(Lead::query()->search($token)->whereKey($lead->id)->exists(), $token);
        }

        $this->assertTrue(Lead::query()->open()->whereKey($lead->id)->exists());
        $this->assertTrue(Lead::query()->closed()->whereKey($closed->id)->exists());
        $this->assertTrue(Lead::query()->overdueFollowUp()->whereKey($lead->id)->exists());
        $this->assertTrue(Lead::query()->dueToday()->whereKey($dueToday->id)->exists());
        $this->assertTrue(Lead::query()->lost()->whereKey($closed->id)->exists());
        $this->assertTrue(Lead::query()->converted()->whereKey($converted->id)->exists());
        $this->assertTrue(Lead::query()->notConverted()->whereKey($lead->id)->exists());
    }

    public function test_duplicate_spam_lost_and_task_overdue_helpers_work(): void
    {
        $original = Lead::factory()->create(['status' => LeadStatusEnum::Contacted]);
        $duplicate = Lead::factory()->create([
            'status' => LeadStatusEnum::Duplicate,
            'duplicate_of_id' => $original->id,
        ]);
        $spam = Lead::factory()->create(['status' => LeadStatusEnum::Spam]);
        $lost = Lead::factory()->create(['status' => LeadStatusEnum::Lost]);
        $task = LeadTask::factory()->create([
            'marketing_lead_id' => $original->id,
            'status' => LeadTaskStatus::Open,
            'due_at' => now()->subDay(),
        ]);

        $this->assertTrue(Lead::query()->duplicates()->whereKey($duplicate->id)->exists());
        $this->assertTrue(Lead::query()->spam()->whereKey($spam->id)->exists());
        $this->assertTrue(Lead::query()->lost()->whereKey($lost->id)->exists());
        $this->assertTrue($duplicate->is_duplicate);
        $this->assertTrue($spam->is_spam);
        $this->assertTrue($lost->is_lost);
        $this->assertTrue($task->is_overdue);
        $this->assertFalse($duplicate->can_be_converted);
    }
}
