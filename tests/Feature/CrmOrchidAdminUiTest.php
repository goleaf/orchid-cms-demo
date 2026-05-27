<?php

namespace Tests\Feature;

use App\Enums\LeadStatus;
use App\Enums\LeadTaskStatus;
use App\Models\LeadLostReason;
use App\Models\MarketingLead;
use App\Models\MarketingLeadTask;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrmOrchidAdminUiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_with_crm_leads_view_can_open_lead_list_and_see_lead(): void
    {
        $this->seed();

        $lead = MarketingLead::factory()->create([
            'lead_number' => 'LEAD-2026-9001',
            'first_name' => 'Admin',
            'last_name' => 'Visible',
        ]);

        $this->actingAs($this->userWithPermissions(['crm.leads.view']))
            ->get(route('platform.crm.leads'))
            ->assertOk()
            ->assertSee('LEAD-2026-9001')
            ->assertSee('Admin Visible')
            ->assertSee(tkey('crm.leads.actions.open'));

        $this->assertDatabaseHas('marketing_leads', ['id' => $lead->id]);
    }

    public function test_user_without_crm_leads_view_cannot_open_lead_list(): void
    {
        $this->seed();

        $this->actingAs($this->userWithPermissions())
            ->get(route('platform.crm.leads'))
            ->assertForbidden();
    }

    public function test_marketing_data_is_hidden_without_marketing_permission(): void
    {
        $this->seed();

        $lead = MarketingLead::factory()->create([
            'utm_source' => 'secret-ad-network',
            'utm_campaign' => 'secret-campaign',
        ]);

        $this->actingAs($this->userWithPermissions(['crm.leads.update']))
            ->get(route('platform.crm.leads.edit', $lead))
            ->assertOk()
            ->assertDontSee('secret-ad-network')
            ->assertDontSee(tkey('crm.leads.fields.utm_source'));

        $this->actingAs($this->userWithPermissions(['crm.leads.update', 'crm.leads.view_marketing']))
            ->get(route('platform.crm.leads.edit', $lead))
            ->assertOk()
            ->assertSee('secret-ad-network')
            ->assertSee(tkey('crm.leads.fields.utm_source'));
    }

    public function test_dictionary_task_and_pipeline_screens_require_permissions(): void
    {
        $this->seed();

        $viewer = $this->userWithPermissions(['crm.leads.view']);

        $this->actingAs($viewer)
            ->get(route('platform.crm.statuses'))
            ->assertForbidden();
        $this->actingAs($viewer)
            ->get(route('platform.crm.tasks'))
            ->assertForbidden();
        $this->actingAs($viewer)
            ->get(route('platform.crm.pipeline'))
            ->assertForbidden();

        $this->actingAs($this->userWithPermissions(['crm.leads.manage_dictionaries']))
            ->get(route('platform.crm.statuses'))
            ->assertOk()
            ->assertSee(tkey('menu.crm.statuses'));

        $task = MarketingLeadTask::factory()->create(['title' => 'CRM permission task']);

        $this->actingAs($this->userWithPermissions(['crm.leads.manage_tasks']))
            ->get(route('platform.crm.tasks'))
            ->assertOk()
            ->assertSee('CRM permission task');

        $this->assertDatabaseHas('marketing_lead_tasks', ['id' => $task->id]);

        $this->actingAs($this->userWithPermissions(['crm.pipeline.view']))
            ->get(route('platform.crm.pipeline'))
            ->assertOk()
            ->assertSee(tkey('crm.pipeline.title'));
    }

    public function test_lead_screen_modal_methods_call_crm_actions(): void
    {
        $this->seed();

        $actor = $this->userWithPermissions([
            'crm.leads.update',
            'crm.leads.assign',
            'crm.leads.change_status',
            'crm.leads.manage_tasks',
        ]);
        $manager = $this->userWithPermissions();
        $lead = MarketingLead::factory()->create([
            'status' => LeadStatus::New,
            'first_name' => 'Action',
            'last_name' => 'Lead',
        ]);

        $this->actingAs($actor)
            ->post(route('platform.crm.leads.edit', ['lead' => $lead, 'method' => 'assignManager']), [
                'manager_id' => $manager->id,
            ])
            ->assertRedirect(route('platform.crm.leads.edit', $lead))
            ->assertSessionHasNoErrors();

        $this->assertSame($manager->id, $lead->refresh()->responsible_manager_id);
        $this->assertDatabaseHas('marketing_lead_activities', [
            'marketing_lead_id' => $lead->id,
            'type' => 'manager_assigned',
        ]);

        $this->actingAs($actor)
            ->post(route('platform.crm.leads.edit', ['lead' => $lead, 'method' => 'addNote']), [
                'comment' => ['body' => 'Internal CRM note'],
            ])
            ->assertRedirect(route('platform.crm.leads.edit', $lead))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('marketing_lead_comments', [
            'marketing_lead_id' => $lead->id,
            'body' => 'Internal CRM note',
        ]);

        $this->actingAs($actor)
            ->post(route('platform.crm.leads.edit', ['lead' => $lead, 'method' => 'logCall']), [
                'call' => [
                    'result' => 'reached',
                    'duration_seconds' => 180,
                    'comment' => 'Reached by phone.',
                    'next_follow_up_at' => now()->addDay()->format('Y-m-d\TH:i'),
                ],
            ])
            ->assertRedirect(route('platform.crm.leads.edit', $lead))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('marketing_lead_communications', [
            'marketing_lead_id' => $lead->id,
            'channel' => 'phone',
            'call_result' => 'reached',
        ]);

        $this->actingAs($actor)
            ->post(route('platform.crm.leads.edit', ['lead' => $lead, 'method' => 'createTask']), [
                'task' => [
                    'title' => 'Prepare CRM documents',
                    'notes' => 'Ask client for documents.',
                    'assigned_to_user_id' => $manager->id,
                    'priority' => 'high',
                    'due_at' => now()->addDay()->format('Y-m-d\TH:i'),
                ],
            ])
            ->assertRedirect(route('platform.crm.leads.edit', $lead))
            ->assertSessionHasNoErrors();

        $task = $lead->tasks()->where('title', 'Prepare CRM documents')->firstOrFail();

        $this->actingAs($actor)
            ->post(route('platform.crm.leads.edit', ['lead' => $lead, 'method' => 'cancelTask']), [
                'task' => $task->id,
                'reason' => 'Handled immediately.',
            ])
            ->assertRedirect(route('platform.crm.leads.edit', $lead))
            ->assertSessionHasNoErrors();

        $this->assertSame(LeadTaskStatus::Cancelled, $task->refresh()->status);

        $this->actingAs($actor)
            ->post(route('platform.crm.leads.edit', ['lead' => $lead, 'method' => 'changeStatus']), [
                'status' => LeadStatus::Consultation->value,
                'reason' => 'Consultation booked.',
            ])
            ->assertRedirect(route('platform.crm.leads.edit', $lead))
            ->assertSessionHasNoErrors();

        $this->assertSame(LeadStatus::Consultation, $lead->refresh()->status);
    }

    public function test_export_button_requires_export_permission(): void
    {
        $this->seed();

        $this->actingAs($this->userWithPermissions(['crm.leads.view']))
            ->get(route('platform.crm.leads'))
            ->assertOk()
            ->assertDontSee(tkey('common.actions.export_csv'));

        $this->actingAs($this->userWithPermissions(['crm.leads.view', 'crm.leads.export']))
            ->get(route('platform.crm.leads'))
            ->assertOk()
            ->assertSee(tkey('common.actions.export_csv'));
    }

    /**
     * @param  array<int, string>  $permissions
     */
    private function userWithPermissions(array $permissions = []): User
    {
        $user = User::factory()->create();

        $user->forceFill([
            'permissions' => collect(['platform.index', 'platform.main'])
                ->merge($permissions)
                ->mapWithKeys(fn (string $permission): array => [$permission => true])
                ->all(),
        ])->save();

        return $user;
    }
}
