<?php

namespace Tests\Feature;

use App\Actions\AddLeadNoteAction;
use App\Actions\AssignLeadManagerAction;
use App\Actions\CancelLeadTaskAction;
use App\Actions\ChangeLeadStatusAction;
use App\Actions\CompleteLeadTaskAction;
use App\Actions\CreateLeadAction;
use App\Actions\CreateLeadTaskAction;
use App\Actions\CreateWebsiteLeadAction;
use App\Actions\DetectLeadDuplicateAction;
use App\Actions\LogLeadCallAction;
use App\Actions\MarkLeadDuplicateAction;
use App\Actions\MarkLeadLostAction;
use App\Actions\MarkLeadSpamAction;
use App\Actions\ReopenLeadAction;
use App\Actions\UpdateLeadAction;
use App\Enums\LeadStatus;
use App\Enums\LeadTaskPriority;
use App\Enums\LeadTaskStatus;
use App\Models\Lead;
use App\Models\LeadLostReason;
use App\Models\MarketingLead;
use App\Models\User;
use App\Notifications\EnrollmentLeadSubmittedNotification;
use App\Rules\DictionaryCodeRule;
use App\Rules\PhoneOrEmailRequiredRule;
use App\Rules\ValidLeadStatusTransitionRule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CrmLeadBusinessActionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_lead_action_creates_manual_crm_lead(): void
    {
        $this->seed();
        $manager = $this->admin();

        $lead = app(CreateLeadAction::class)->handle([
            'full_name' => 'Manual CRM Lead',
            'phone' => '00 370 (600) 111-22',
            'email' => 'manual.crm@example.com',
            'source' => 'phone',
            'responsible_manager_id' => $manager->id,
            'priority' => 'high',
            'consent_accepted' => true,
        ], $manager);

        $this->assertSame('manual.crm@example.com', $lead->email);
        $this->assertSame('+37060011122', $lead->phone);
        $this->assertSame('+37060011122', $lead->normalized_phone);
        $this->assertSame(LeadStatus::New, $lead->status);
        $this->assertStringStartsWith('LEAD-'.now()->format('Y').'-', (string) $lead->lead_number);
        $this->assertSame($manager->id, $lead->created_by_user_id);
        $this->assertTrue($lead->activities()->where('type', 'created_manually')->exists());
        $this->assertTrue($lead->tasks()->where('status', LeadTaskStatus::Open->value)->exists());
    }

    public function test_create_website_lead_action_creates_public_crm_lead(): void
    {
        Notification::fake();
        $this->seed();

        $lead = app(CreateWebsiteLeadAction::class)->handle([
            'full_name' => 'Website CRM Lead',
            'phone' => '+370 600 22222',
            'email' => 'website.crm@example.com',
            'source' => 'website',
            'form_name' => 'enrollment',
            'consent_accepted' => true,
            'utm_source' => 'google',
            'landing_page' => 'https://example.test/',
            'form_page' => 'https://example.test/courses',
        ]);

        $this->assertSame('website', $lead->source);
        $this->assertSame('enrollment', $lead->form_name);
        $this->assertSame('google', $lead->utm_source);
        $this->assertTrue($lead->consent_accepted);
        $this->assertNotNull($lead->lead_number);
        $this->assertTrue($lead->activities()->where('type', 'created_from_website')->exists());
        $this->assertTrue($lead->tasks()->exists());
        Notification::assertSentTo($this->admin(), EnrollmentLeadSubmittedNotification::class);
    }

    public function test_update_lead_action_updates_lead_and_records_activity(): void
    {
        $this->seed();
        $lead = Lead::factory()->create([
            'phone' => '+37060033333',
            'email' => 'old@example.com',
            'status' => LeadStatus::Contacted,
        ]);

        $updated = app(UpdateLeadAction::class)->handle($lead, [
            'phone' => '+370 (600) 444-44',
            'email' => 'updated@example.com',
            'internal_comment' => 'Updated through action',
        ], $this->admin());

        $this->assertSame('+37060044444', $updated->phone);
        $this->assertSame('updated@example.com', $updated->email);
        $this->assertSame('Updated through action', $updated->internal_comment);
        $this->assertTrue($updated->activities()->where('type', 'updated')->exists());
    }

    public function test_change_status_action_validates_transition(): void
    {
        $this->seed();
        $lead = Lead::factory()->create(['status' => LeadStatus::New]);

        try {
            app(ChangeLeadStatusAction::class)->handle($lead, LeadStatus::ReadyToEnroll, $this->admin());
            $this->fail('Invalid transition did not throw.');
        } catch (ValidationException $exception) {
            $this->assertSame(tkey('crm.leads.validation.invalid_status_transition'), $exception->errors()['status'][0]);
        }

        $lead = app(ChangeLeadStatusAction::class)->handle($lead->refresh(), LeadStatus::Contacted, $this->admin());

        $this->assertSame(LeadStatus::Contacted, $lead->status);
        $this->assertTrue($lead->activities()->where('type', 'status_changed')->exists());
    }

    public function test_manager_note_call_task_and_completion_actions_work(): void
    {
        $this->seed();
        $manager = $this->admin();
        $lead = Lead::factory()->create(['status' => LeadStatus::New]);

        $lead = app(AssignLeadManagerAction::class)->handle($lead, $manager, $manager);
        $this->assertSame($manager->id, $lead->responsible_manager_id);
        $this->assertTrue($lead->activities()->where('type', 'manager_assigned')->exists());

        app(AddLeadNoteAction::class)->handle($lead, $manager, 'Internal note');
        $this->assertTrue($lead->comments()->where('body', 'Internal note')->exists());
        $this->assertTrue($lead->activities()->where('type', 'note_added')->exists());

        app(LogLeadCallAction::class)->handle($lead->refresh(), $manager, 'reached', 90, 'Reached by phone');
        $this->assertNotNull($lead->refresh()->last_contacted_at);
        $this->assertTrue($lead->callLogs()->where('call_result', 'reached')->exists());

        $task = app(CreateLeadTaskAction::class)->handle(
            $lead->refresh(),
            $manager,
            'Follow up',
            now()->addHour(),
            LeadTaskPriority::High,
        );
        $this->assertSame($manager->id, $task->assigned_to_user_id);

        $task = app(CompleteLeadTaskAction::class)->handle($task, $manager);
        $this->assertSame(LeadTaskStatus::Done, $task->status);
        $this->assertNotNull($task->completed_at);

        $cancelled = app(CreateLeadTaskAction::class)->handle($lead->refresh(), $manager, 'Cancel me', now()->addDay());
        $cancelled = app(CancelLeadTaskAction::class)->handle($cancelled, $manager, 'No longer needed');
        $this->assertSame(LeadTaskStatus::Cancelled, $cancelled->status);
        $this->assertNotNull($cancelled->cancelled_at);
    }

    public function test_lost_duplicate_spam_and_reopen_actions_work(): void
    {
        $this->seed();
        $manager = $this->admin();
        $lostReason = LeadLostReason::query()->where('code', 'price')->firstOrFail();

        $lost = Lead::factory()->create(['status' => LeadStatus::Contacted]);
        $lost = app(MarkLeadLostAction::class)->handle($lost, $lostReason->code, 'Price objection', $manager);
        $this->assertSame(LeadStatus::Lost, $lost->status);
        $this->assertNotNull($lost->closed_at);
        $this->assertSame('price', $lost->lost_reason_code);
        $this->assertTrue($lost->activities()->where('type', 'marked_lost')->exists());

        $original = Lead::factory()->create(['status' => LeadStatus::Contacted]);
        $duplicate = Lead::factory()->create(['status' => LeadStatus::Contacted]);
        $duplicate = app(MarkLeadDuplicateAction::class)->handle($duplicate, $original->id, 'Same client', $manager);
        $this->assertSame($original->id, $duplicate->duplicate_of_id);
        $this->assertSame(LeadStatus::Duplicate, $duplicate->status);
        $this->assertNotNull($duplicate->closed_at);

        $spam = Lead::factory()->create(['status' => LeadStatus::New]);
        $spam = app(MarkLeadSpamAction::class)->handle($spam, $manager);
        $this->assertSame(LeadStatus::Spam, $spam->status);
        $this->assertNotNull($spam->closed_at);

        $reopened = app(ReopenLeadAction::class)->handle($lost->refresh(), $manager, LeadStatus::Contacted, 'Client returned');
        $this->assertSame(LeadStatus::Contacted, $reopened->status);
        $this->assertNull($reopened->closed_at);
        $this->assertTrue($reopened->activities()->where('type', 'reopened')->exists());
    }

    public function test_duplicate_detection_prefers_phone_and_checks_email_case_insensitively(): void
    {
        $emailOriginal = Lead::factory()->create([
            'phone' => '+37060055555',
            'email' => 'Case.Match@example.com',
            'created_at' => now()->subDays(2),
        ]);
        $phoneOriginal = Lead::factory()->create([
            'phone' => '+37060066666',
            'email' => 'other@example.com',
            'created_at' => now()->subDay(),
        ]);

        $phoneMatch = app(DetectLeadDuplicateAction::class)->handle([
            'phone' => '370 600 66666',
            'email' => 'case.match@example.com',
        ]);
        $emailMatch = app(DetectLeadDuplicateAction::class)->handle([
            'email' => 'case.match@example.com',
        ]);

        $this->assertTrue($phoneMatch?->is($phoneOriginal));
        $this->assertTrue($emailMatch?->is($emailOriginal));
    }

    public function test_validation_errors_are_translated(): void
    {
        $this->seed();
        $lead = Lead::factory()->create(['status' => LeadStatus::New]);

        $validator = Validator::make(
            [
                'contact' => null,
                'phone' => null,
                'email' => null,
                'status' => 'ready_to_enroll',
                'code' => 'Invalid Code',
            ],
            [
                'contact' => [new PhoneOrEmailRequiredRule('phone', 'email', 'crm.leads.validation.phone_or_email_required')],
                'status' => [new ValidLeadStatusTransitionRule($lead, $this->admin())],
                'code' => [new DictionaryCodeRule],
            ],
        );

        $this->assertTrue($validator->fails());
        $this->assertSame(tkey('crm.leads.validation.phone_or_email_required'), $validator->errors()->first('contact'));
        $this->assertSame(tkey('crm.leads.validation.invalid_status_transition'), $validator->errors()->first('status'));
        $this->assertSame(tkey('crm.leads.validation.invalid_dictionary_code'), $validator->errors()->first('code'));
        $this->assertNotSame('crm.leads.validation.invalid_dictionary_code', tkey('crm.leads.validation.invalid_dictionary_code'));
    }

    private function admin(): User
    {
        return User::query()
            ->where('email', 'admin@example.com')
            ->firstOrFail();
    }
}
