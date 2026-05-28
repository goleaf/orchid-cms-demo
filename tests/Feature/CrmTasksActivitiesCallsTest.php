<?php

namespace Tests\Feature;

use App\Actions\AddLeadNoteAction;
use App\Actions\CancelLeadTaskAction;
use App\Actions\CompleteLeadTaskAction;
use App\Actions\CreateLeadAction;
use App\Actions\CreateLeadTaskAction;
use App\Actions\CreateWebsiteLeadAction;
use App\Actions\LogLeadCallAction;
use App\Enums\LeadStatus;
use App\Enums\LeadTaskPriority;
use App\Enums\LeadTaskStatus;
use App\Models\Lead;
use App\Models\LeadTask;
use App\Models\MarketingLeadActivity;
use App\Models\MarketingLeadTask;
use App\Models\User;
use Database\Seeders\CrmTranslationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class CrmTasksActivitiesCallsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CrmTranslationSeeder::class);
    }

    public function test_note_creates_activity(): void
    {
        $manager = User::factory()->create();
        $lead = Lead::factory()->create();

        app(AddLeadNoteAction::class)->handle($lead, $manager, 'Call back after work.');

        $this->assertDatabaseHas('marketing_lead_comments', [
            'marketing_lead_id' => $lead->id,
            'user_id' => $manager->id,
            'body' => 'Call back after work.',
            'is_internal' => true,
        ]);
        $this->assertDatabaseHas('marketing_lead_activities', [
            'marketing_lead_id' => $lead->id,
            'user_id' => $manager->id,
            'type' => 'note_added',
            'body' => 'Call back after work.',
        ]);
    }

    public function test_call_log_updates_follow_up_and_creates_activity(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-05-28 09:00:00'));

        try {
            $manager = User::factory()->create();
            $lead = Lead::factory()->create(['status' => LeadStatus::New]);
            $nextFollowUp = now()->addHours(3);

            app(LogLeadCallAction::class)->handle(
                $lead,
                $manager,
                'reached',
                120,
                'Client reached.',
                $nextFollowUp,
            );

            $lead->refresh();

            $this->assertSame(LeadStatus::Contacted, $lead->status);
            $this->assertNotNull($lead->last_contacted_at);
            $this->assertTrue($lead->next_follow_up_at?->equalTo($nextFollowUp));
            $this->assertDatabaseHas('marketing_lead_communications', [
                'marketing_lead_id' => $lead->id,
                'user_id' => $manager->id,
                'channel' => 'phone',
                'direction' => 'outbound',
                'call_result' => 'reached',
                'duration_seconds' => 120,
            ]);
            $this->assertDatabaseHas('marketing_lead_activities', [
                'marketing_lead_id' => $lead->id,
                'type' => 'call_logged',
            ]);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_refused_call_does_not_mark_lost_without_reason(): void
    {
        $manager = User::factory()->create();
        $lead = Lead::factory()->create(['status' => LeadStatus::Contacted]);

        app(LogLeadCallAction::class)->handle($lead, $manager, 'refused', 30, 'No details.');

        $this->assertSame(LeadStatus::Contacted, $lead->refresh()->status);
        $this->assertNull($lead->lost_reason_code);
        $this->assertFalse($lead->activities()->where('type', 'marked_lost')->exists());
    }

    public function test_refused_call_with_lost_reason_marks_lost(): void
    {
        $manager = User::factory()->create();
        $lead = Lead::factory()->create(['status' => LeadStatus::Contacted]);

        app(LogLeadCallAction::class)->handle($lead, $manager, 'refused', 30, 'Too expensive.', null, 'price');

        $lead->refresh();

        $this->assertSame(LeadStatus::Lost, $lead->status);
        $this->assertSame('price', $lead->lost_reason_code);
        $this->assertNotNull($lead->closed_at);
        $this->assertTrue($lead->activities()->where('type', 'marked_lost')->exists());
    }

    public function test_first_website_task_is_created(): void
    {
        Notification::fake();
        Carbon::setTestNow(Carbon::parse('2026-05-28 09:00:00'));

        try {
            User::factory()->create(['email' => 'admin@example.com']);

            $lead = app(CreateWebsiteLeadAction::class)->handle([
                'full_name' => 'Website Task Lead',
                'phone' => '+370 600 33333',
                'email' => 'website-task@example.com',
                'form_name' => 'enrollment',
                'consent_accepted' => true,
            ]);

            $task = $lead->tasks()->firstOrFail();

            $this->assertSame(tkey('crm.tasks.defaults.contact_new_website_lead'), $task->title);
            $this->assertSame(LeadTaskPriority::High, $task->priority);
            $this->assertSame(LeadTaskStatus::Open, $task->status);
            $this->assertTrue($task->due_at?->equalTo(now()->addMinutes(30)));
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_manual_lead_first_task_is_created(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-05-28 09:00:00'));

        try {
            $manager = User::factory()->create();

            $lead = app(CreateLeadAction::class)->handle([
                'full_name' => 'Manual Task Lead',
                'phone' => '+370 600 44444',
                'source' => 'phone',
            ], $manager);

            $task = $lead->tasks()->firstOrFail();

            $this->assertSame(tkey('crm.tasks.defaults.contact_new_manual_lead'), $task->title);
            $this->assertSame(tkey('crm.tasks.system_notes.new_manual_lead_reminder'), $task->notes);
            $this->assertSame(LeadTaskPriority::Normal, $task->priority);
            $this->assertSame(LeadTaskStatus::Open, $task->status);
            $this->assertTrue($task->due_at?->equalTo(now()->addDay()));
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_task_can_be_completed_and_cancelled(): void
    {
        $manager = User::factory()->create();
        $lead = Lead::factory()->create();

        $task = app(CreateLeadTaskAction::class)->handle(
            $lead,
            $manager,
            'Prepare consultation',
            now()->addHour(),
            LeadTaskPriority::High,
        );

        $completed = app(CompleteLeadTaskAction::class)->handle($task, $manager);

        $this->assertSame(LeadTaskStatus::Done, $completed->status);
        $this->assertNotNull($completed->completed_at);
        $this->assertDatabaseHas('marketing_lead_activities', [
            'marketing_lead_id' => $lead->id,
            'type' => 'task_completed',
        ]);

        $secondTask = app(CreateLeadTaskAction::class)->handle($lead->refresh(), $manager, 'Cancel me', now()->addDay());
        $cancelled = app(CancelLeadTaskAction::class)->handle($secondTask, $manager, 'No longer required.');

        $this->assertSame(LeadTaskStatus::Cancelled, $cancelled->status);
        $this->assertNotNull($cancelled->cancelled_at);
        $this->assertDatabaseHas('marketing_lead_activities', [
            'marketing_lead_id' => $lead->id,
            'type' => 'task_cancelled',
        ]);
    }

    public function test_overdue_and_due_today_task_scopes_work(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-05-28 09:00:00'));

        try {
            $lead = Lead::factory()->create();
            $openOverdue = LeadTask::factory()->open()->create([
                'marketing_lead_id' => $lead->id,
                'due_at' => now()->subHour(),
            ]);
            $inProgressOverdue = LeadTask::factory()->inProgress()->create([
                'marketing_lead_id' => $lead->id,
                'due_at' => now()->subMinutes(30),
            ]);
            $doneOverdue = LeadTask::factory()->done()->create([
                'marketing_lead_id' => $lead->id,
                'due_at' => now()->subHour(),
            ]);
            $cancelledOverdue = LeadTask::factory()->cancelled()->create([
                'marketing_lead_id' => $lead->id,
                'due_at' => now()->subHour(),
            ]);
            $dueToday = LeadTask::factory()->inProgress()->create([
                'marketing_lead_id' => $lead->id,
                'due_at' => now()->addHours(2),
            ]);

            $overdueIds = MarketingLeadTask::query()->overdue()->pluck('id')->all();
            $dueTodayIds = MarketingLeadTask::query()->dueToday()->pluck('id')->all();

            $this->assertContains($openOverdue->id, $overdueIds);
            $this->assertContains($inProgressOverdue->id, $overdueIds);
            $this->assertNotContains($doneOverdue->id, $overdueIds);
            $this->assertNotContains($cancelledOverdue->id, $overdueIds);
            $this->assertContains($dueToday->id, $dueTodayIds);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_activity_type_labels_are_translated(): void
    {
        app()->setLocale('en');

        $activity = MarketingLeadActivity::factory()->create(['type' => 'call_logged']);

        $this->assertSame('Call logged', $activity->typeLabel());
    }
}
