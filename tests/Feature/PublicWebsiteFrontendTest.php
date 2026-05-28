<?php

namespace Tests\Feature;

use App\Enums\GroupStatus;
use App\Enums\LeadTaskPriority;
use App\Enums\LeadTaskStatus;
use App\Models\Branch;
use App\Models\Course;
use App\Models\MarketingLead;
use App\Models\TrainingGroup;
use App\Models\TrainingProgram;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PublicWebsiteFrontendTest extends TestCase
{
    use RefreshDatabase;

    public function test_required_public_website_pages_load(): void
    {
        $this->seed();

        $program = TrainingProgram::query()
            ->where('slug', 'category-b-manual')
            ->firstOrFail();
        $branch = Branch::query()
            ->where('slug', 'vilnius-main')
            ->firstOrFail();

        collect([
            route('website.home') => tkey('website.home.programs.title', [], 'ru'),
            route('website.courses.index') => tkey('website.courses.title', [], 'ru'),
            route('website.courses.show', $program) => $program->displayTitle(),
            route('website.pricing') => tkey('website.prices.title', [], 'ru'),
            route('website.branches.index') => tkey('website.branches.title', [], 'ru'),
            route('website.branches.show', ['branch' => $branch->slug]) => $branch->displayName(),
            route('website.contacts') => tkey('website.contacts.title', [], 'ru'),
            route('website.thank_you') => tkey('website.forms.messages.thank_you_title', [], 'ru'),
        ])->each(function (string $needle, string $url): void {
            $this->get($url)
                ->assertOk()
                ->assertSee($needle);
        });
    }

    public function test_application_form_creates_lead_with_utm_and_selected_context(): void
    {
        Notification::fake();
        $this->seed();

        $program = TrainingProgram::query()
            ->where('slug', 'category-b-manual')
            ->firstOrFail();
        $branch = Branch::query()
            ->where('slug', 'vilnius-main')
            ->firstOrFail();
        $group = TrainingGroup::query()
            ->where('code', 'B-VNO-001')
            ->firstOrFail();

        $this->get('/courses?utm_source=google&utm_medium=cpc&utm_campaign=frontend');

        $this->post(route('website.leads.store'), [
            'course_id' => $program->id,
            'branch_id' => $branch->id,
            'training_group_id' => $group->id,
            'full_name' => 'Frontend Lead',
            'phone' => '+370 600 12345',
            'email' => 'frontend.lead@example.com',
            'preferred_format' => 'mixed',
            'preferred_language' => 'en',
            'preferred_time' => 'Evenings',
            'preferred_messenger' => 'Telegram',
            'comment' => 'Interested in this group.',
            'consent_accepted' => '1',
            'form_name' => 'frontend_test_application',
        ])
            ->assertRedirect(route('website.thank_you'))
            ->assertSessionHasNoErrors();

        $lead = MarketingLead::query()
            ->where('email', 'frontend.lead@example.com')
            ->firstOrFail();

        $this->assertSame('website', $lead->source);
        $this->assertSame('new', $lead->status->value);
        $this->assertSame('frontend_test_application', $lead->form_name);
        $this->assertTrue($lead->consent_accepted);
        $this->assertNotNull($lead->consent_accepted_at);
        $this->assertSame('google', $lead->utm_source);
        $this->assertSame('frontend', $lead->utm_campaign);
        $this->assertSame($program->id, $lead->training_program_id);
        $this->assertSame($branch->id, $lead->branch_id);
        $this->assertSame($group->id, $lead->training_group_id);
        $this->assertSame('Interested in this group.', $lead->message);
        $this->assertStringContainsString('utm_source=google', (string) $lead->landing_page);
        $this->assertTrue($lead->activities()->where('type', 'created_from_website')->exists());

        $task = $lead->tasks()->firstOrFail();
        $this->assertSame(tkey('crm.tasks.defaults.contact_new_website_lead'), $task->title);
        $this->assertSame(LeadTaskPriority::High, $task->priority);
        $this->assertSame(LeadTaskStatus::Open, $task->status);
        $this->assertNotNull($task->due_at);
    }

    public function test_application_form_marks_possible_duplicate_without_blocking_creation(): void
    {
        Notification::fake();
        $this->seed();

        $program = TrainingProgram::query()
            ->where('slug', 'category-b-manual')
            ->firstOrFail();
        $branch = Branch::query()
            ->where('slug', 'vilnius-main')
            ->firstOrFail();
        $original = MarketingLead::factory()->create([
            'first_name' => 'Original',
            'last_name' => 'Application',
            'phone' => '+999 111 222333',
            'email' => 'original.application@example.com',
        ]);

        $this->post(route('website.leads.store'), [
            'course_id' => $program->id,
            'branch_id' => $branch->id,
            'full_name' => 'Duplicate Application',
            'phone' => '999111222333',
            'email' => 'duplicate.application@example.com',
            'preferred_format' => 'mixed',
            'preferred_language' => 'en',
            'consent_accepted' => '1',
        ])
            ->assertRedirect(route('website.thank_you'))
            ->assertSessionHasNoErrors();

        $duplicate = MarketingLead::query()
            ->where('email', 'duplicate.application@example.com')
            ->firstOrFail();

        $this->assertSame($original->id, $duplicate->duplicate_of_id);
        $this->assertSame('new', $duplicate->status->value);
        $this->assertTrue($duplicate->activities()->where('type', 'created_from_website')->exists());
        $this->assertDatabaseHas('marketing_lead_activities', [
            'marketing_lead_id' => $duplicate->id,
            'type' => 'marked_duplicate',
            'new_value' => (string) $original->id,
        ]);
    }

    public function test_callback_and_contact_forms_create_leads(): void
    {
        Notification::fake();
        $this->seed();

        $this->post(route('website.callback.store'), [
            'full_name' => 'Callback Lead',
            'phone' => '+370 600 22222',
            'callback_time' => 'Tomorrow morning',
            'comment' => 'Call me back.',
            'consent_accepted' => '1',
        ])
            ->assertRedirect(route('website.thank_you'))
            ->assertSessionHasNoErrors();

        $this->post(route('website.contact.store'), [
            'full_name' => 'Contact Lead',
            'phone' => '+370 600 33333',
            'email' => 'contact.lead@example.com',
            'comment' => 'I have a question.',
            'consent_accepted' => '1',
        ])
            ->assertRedirect(route('website.thank_you'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('marketing_leads', [
            'full_name' => 'Callback Lead',
            'source' => 'callback',
            'form_name' => 'callback',
            'preferred_time' => 'Tomorrow morning',
            'message' => 'Call me back.',
        ]);
        $this->assertDatabaseHas('marketing_leads', [
            'full_name' => 'Contact Lead',
            'source' => 'contact_form',
            'form_name' => 'contact',
            'message' => 'I have a question.',
        ]);

        $callbackLead = MarketingLead::query()
            ->where('full_name', 'Callback Lead')
            ->firstOrFail();
        $contactLead = MarketingLead::query()
            ->where('full_name', 'Contact Lead')
            ->firstOrFail();

        foreach ([$callbackLead, $contactLead] as $lead) {
            $this->assertSame('new', $lead->status->value);
            $this->assertTrue($lead->consent_accepted);
            $this->assertTrue($lead->activities()->where('type', 'created_from_website')->exists());

            $task = $lead->tasks()->firstOrFail();
            $this->assertSame(tkey('crm.tasks.defaults.contact_new_website_lead'), $task->title);
            $this->assertSame(LeadTaskPriority::High, $task->priority);
            $this->assertSame(LeadTaskStatus::Open, $task->status);
        }
    }

    public function test_public_frontend_hides_hidden_catalog_and_full_groups(): void
    {
        $this->seed();

        $visibleProgram = TrainingProgram::query()
            ->where('slug', 'category-b-manual')
            ->firstOrFail();
        $visibleBranch = Branch::query()
            ->where('slug', 'vilnius-main')
            ->firstOrFail();

        Course::factory()->create([
            'title' => 'Hidden Frontend Course',
            'title_translations' => ['ru' => 'Hidden Frontend Course'],
            'is_active' => true,
            'is_visible_on_site' => false,
        ]);
        Branch::factory()->create([
            'name' => 'Hidden Frontend Branch',
            'name_translations' => ['ru' => 'Hidden Frontend Branch'],
            'is_active' => true,
            'is_visible_on_site' => false,
        ]);
        TrainingGroup::factory()->create([
            'training_program_id' => $visibleProgram->id,
            'branch_id' => $visibleBranch->id,
            'name' => 'Full Frontend Group',
            'name_translations' => ['ru' => 'Full Frontend Group'],
            'status' => GroupStatus::Recruiting,
            'is_visible_on_site' => true,
            'capacity' => 8,
            'places_taken' => 8,
        ]);

        $this->get(route('website.courses.index'))
            ->assertOk()
            ->assertDontSee('Hidden Frontend Course')
            ->assertDontSee('Full Frontend Group');

        $this->get(route('website.branches.index'))
            ->assertOk()
            ->assertDontSee('Hidden Frontend Branch')
            ->assertDontSee('Full Frontend Group');
    }

    public function test_public_form_validation_errors_are_translated(): void
    {
        $this->seed();

        $program = TrainingProgram::query()
            ->where('slug', 'category-b-manual')
            ->firstOrFail();
        $branch = Branch::query()
            ->where('slug', 'vilnius-main')
            ->firstOrFail();

        $this->from(route('website.home'))
            ->post(route('website.leads.store'), [
                'course_id' => $program->id,
                'branch_id' => $branch->id,
                'full_name' => 'No Consent',
                'phone' => '+370 600 44444',
                'preferred_format' => 'mixed',
                'preferred_language' => 'ru',
            ])
            ->assertRedirect(route('website.home'))
            ->assertSessionHasErrors([
                'consent_accepted' => tkey('website.validation.consent_required', [], 'ru'),
            ]);
    }
}
