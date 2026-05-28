<?php

namespace Tests\Feature;

use App\Actions\FilterLeadsAction;
use App\Actions\GetLeadPipelineAction;
use App\Enums\GroupStatus;
use App\Enums\LeadStatus;
use App\Models\Branch;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\Lead;
use App\Models\MarketingLead;
use App\Models\TrainingGroup;
use App\Models\User;
use Database\Seeders\CrmStatusSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrmLeadFiltersPipelineTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_by_name_phone_email_and_crm_identifiers_works(): void
    {
        $lead = Lead::factory()->create([
            'uuid' => '9d19d27e-7af0-4c62-a12b-advanced-search',
            'lead_number' => 'LEAD-2026-SEARCH',
            'full_name' => 'Advanced Search Driver',
            'first_name' => 'Advanced',
            'last_name' => 'Driver',
            'phone' => '+37060000000',
            'email' => 'advanced.search@example.com',
            'message' => 'Public comment lookup',
            'internal_comment' => 'Internal note lookup',
        ]);
        $other = Lead::factory()->create([
            'full_name' => 'Unrelated Person',
            'phone' => '+37061111111',
            'email' => 'unrelated@example.com',
        ]);

        foreach ([
            'Advanced Search',
            '600 00000',
            'advanced.search@example.com',
            '9d19d27e-7af0',
            'LEAD-2026-SEARCH',
            'Public comment lookup',
            'Internal note lookup',
        ] as $search) {
            $ids = $this->filteredIds(['search' => $search]);

            $this->assertContains($lead->id, $ids, $search);
            $this->assertNotContains($other->id, $ids, $search);
        }

        $this->assertContains($lead->id, $this->filteredIds(['search' => (string) $lead->id]));
    }

    public function test_required_quick_segments_work(): void
    {
        $manager = User::factory()->create();
        $newLead = Lead::factory()->newLead()->create(['lead_number' => 'SEG-NEW']);
        $myLead = Lead::factory()->contacted()->assigned($manager)->create(['lead_number' => 'SEG-MY']);
        $unassignedLead = Lead::factory()->contacted()->unassigned()->create(['lead_number' => 'SEG-UNASSIGNED']);
        $overdueLead = Lead::factory()->overdue()->create(['lead_number' => 'SEG-OVERDUE']);
        $dueTodayLead = Lead::factory()->dueToday()->create(['lead_number' => 'SEG-DUE-TODAY']);
        $originalLead = Lead::factory()->contacted()->create(['lead_number' => 'SEG-ORIGINAL']);
        $duplicateLead = Lead::factory()->duplicate($originalLead)->create(['lead_number' => 'SEG-DUPLICATE']);
        $convertedLead = Lead::factory()->converted()->create(['lead_number' => 'SEG-CONVERTED']);
        $readyLead = Lead::factory()->readyToEnroll()->create(['lead_number' => 'SEG-READY']);

        $this->assertContains($newLead->id, $this->filteredIds(['segment' => 'new']));
        $this->assertContains($myLead->id, $this->filteredIds(['segment' => 'my_leads'], $manager));
        $this->assertNotContains($unassignedLead->id, $this->filteredIds(['segment' => 'my_leads'], $manager));
        $this->assertContains($unassignedLead->id, $this->filteredIds(['segment' => 'unassigned']));
        $this->assertContains($overdueLead->id, $this->filteredIds(['segment' => 'overdue']));
        $this->assertContains($dueTodayLead->id, $this->filteredIds(['segment' => 'call_today']));
        $this->assertContains($duplicateLead->id, $this->filteredIds(['segment' => 'duplicates']));
        $this->assertContains($convertedLead->id, $this->filteredIds(['segment' => 'converted']));
        $this->assertContains($readyLead->id, $this->filteredIds(['segment' => 'ready_to_enroll']));
    }

    public function test_boolean_filters_for_due_today_duplicates_and_conversion_work(): void
    {
        $dueTodayLead = Lead::factory()->dueToday()->create();
        $originalLead = Lead::factory()->contacted()->create();
        $duplicateLead = Lead::factory()->duplicate($originalLead)->create();
        $convertedLead = Lead::factory()->converted()->create();
        $notConvertedLead = Lead::factory()->contacted()->notConverted()->create();

        $this->assertContains($dueTodayLead->id, $this->filteredIds(['only_due_today' => '1']));
        $this->assertContains($duplicateLead->id, $this->filteredIds(['only_duplicates' => '1']));
        $this->assertContains($convertedLead->id, $this->filteredIds(['only_converted' => '1']));

        $notConvertedIds = $this->filteredIds(['only_not_converted' => '1']);
        $this->assertContains($notConvertedLead->id, $notConvertedIds);
        $this->assertNotContains($convertedLead->id, $notConvertedIds);
    }

    public function test_advanced_field_filters_work_together(): void
    {
        $manager = User::factory()->create();
        $category = CourseCategory::factory()->create();
        $course = Course::factory()->create(['course_category_id' => $category->id]);
        $branch = Branch::factory()->create();
        $group = TrainingGroup::factory()->create([
            'training_program_id' => $course->id,
            'course_category_id' => $category->id,
            'branch_id' => $branch->id,
            'status' => GroupStatus::Recruiting,
        ]);
        $lead = Lead::factory()
            ->forTrainingGroup($group)
            ->assigned($manager)
            ->create([
                'source' => 'google_ads',
                'status' => LeadStatus::WaitingPayment,
                'lost_reason_code' => 'price',
                'priority' => 'urgent',
                'next_follow_up_at' => now(),
                'last_contacted_at' => now(),
                'utm_source' => 'google',
                'utm_medium' => 'cpc',
                'utm_campaign' => 'advanced-filters',
                'form_name' => 'application',
                'created_at' => now(),
            ]);
        $control = Lead::factory()->create([
            'source' => 'website',
            'priority' => 'normal',
            'utm_source' => 'organic',
        ]);

        $ids = $this->filteredIds([
            'status' => LeadStatus::WaitingPayment->value,
            'source' => 'google_ads',
            'manager_id' => $manager->id,
            'course_id' => $course->id,
            'course_category_id' => $category->id,
            'branch_id' => $branch->id,
            'training_group_id' => $group->id,
            'lost_reason_code' => 'price',
            'priority' => 'urgent',
            'created_from' => today()->toDateString(),
            'created_to' => today()->toDateString(),
            'next_follow_up_from' => today()->toDateString(),
            'next_follow_up_to' => today()->toDateString(),
            'last_contacted_from' => today()->toDateString(),
            'last_contacted_to' => today()->toDateString(),
            'utm_source' => 'google',
            'utm_medium' => 'cpc',
            'utm_campaign' => 'advanced-filters',
            'form_name' => 'application',
        ]);

        $this->assertContains($lead->id, $ids);
        $this->assertNotContains($control->id, $ids);
    }

    public function test_pipeline_groups_leads_by_active_status_and_limits_cards_per_column(): void
    {
        $this->seed(CrmStatusSeeder::class);

        Lead::factory()
            ->count(24)
            ->newLead()
            ->create();
        $visibleNewLead = Lead::factory()->newLead()->create([
            'lead_number' => 'PIPE-NEW',
            'is_hot' => true,
            'lead_score' => 100,
        ]);
        $contactedLead = Lead::factory()->contacted()->create(['lead_number' => 'PIPE-CONTACTED']);

        $pipeline = app(GetLeadPipelineAction::class)->handle([
            'limit_per_column' => 20,
        ]);

        $this->assertArrayHasKey(LeadStatus::New->value, $pipeline['columns']);
        $this->assertArrayHasKey(LeadStatus::Contacted->value, $pipeline['columns']);
        $this->assertLessThanOrEqual(20, $pipeline['columns'][LeadStatus::New->value]->count());
        $this->assertTrue($pipeline['columns'][LeadStatus::New->value]->contains('id', $visibleNewLead->id));
        $this->assertTrue($pipeline['columns'][LeadStatus::Contacted->value]->contains('id', $contactedLead->id));
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<int, int>
     */
    private function filteredIds(array $filters, ?User $user = null): array
    {
        return app(FilterLeadsAction::class)
            ->handle(MarketingLead::query()->forLeadList(), $filters, $user, true)
            ->pluck('id')
            ->map(fn (int|string $id): int => (int) $id)
            ->all();
    }
}
