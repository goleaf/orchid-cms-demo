<?php

namespace Tests\Feature;

use App\Enums\LeadStatus;
use App\Models\Branch;
use App\Models\MarketingLead;
use App\Models\TrainingGroup;
use App\Models\TrainingProgram;
use App\Models\User;
use App\Notifications\EnrollmentLeadAutoReplyNotification;
use App\Notifications\EnrollmentLeadSubmittedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class DrivingSchoolPlatformTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeded_public_site_renders_auto_school_content(): void
    {
        $this->seed();

        $this->get('/')
            ->assertOk()
            ->assertSee('Driving lessons, exams, and school operations')
            ->assertSee('Student CRM and cabinet base')
            ->assertSee('Программы, цены и часы обучения')
            ->assertSee('Ближайшие группы');
    }

    public function test_public_auto_school_pages_render_seeded_catalog(): void
    {
        $this->seed();

        $program = TrainingProgram::query()
            ->where('slug', 'category-b-manual')
            ->firstOrFail();

        collect([
            route('site.categories.show', $program) => 'Category B Manual',
            route('site.apply') => 'Запись в автошколу',
            route('site.instructors') => 'Инструкторы автошколы',
            route('site.fleet') => 'Автопарк',
            route('site.reviews') => 'Отзывы учеников',
            route('site.blog.index') => 'Блог и база знаний',
            route('site.contacts') => 'Филиалы и контакты',
            route('site.sitemap') => '<urlset',
            route('site.robots') => 'Sitemap:',
        ])->each(function (string $needle, string $url): void {
            $this->get($url)
                ->assertOk()
                ->assertSee($needle, false);
        });
    }

    public function test_online_enrollment_creates_crm_lead_and_notifications(): void
    {
        Notification::fake();
        $this->seed();

        $admin = User::query()
            ->where('email', 'admin@example.com')
            ->firstOrFail();
        $program = TrainingProgram::query()
            ->where('slug', 'category-b-manual')
            ->firstOrFail();
        $branch = Branch::query()
            ->where('slug', 'vilnius-main')
            ->firstOrFail();
        $group = TrainingGroup::query()
            ->where('code', 'B-VNO-001')
            ->firstOrFail();

        $this->from(route('site.apply'))
            ->post(route('site.apply.store'), [
                'training_program_id' => $program->id,
                'branch_id' => $branch->id,
                'training_group_id' => $group->id,
                'first_name' => 'Ieva',
                'last_name' => 'Norkute',
                'email' => 'ieva@example.com',
                'phone' => '+370 600 44444',
                'messenger' => 'WhatsApp',
                'city' => 'Vilnius',
                'preferred_format' => 'mixed',
                'preferred_language' => 'English',
                'preferred_time' => 'Weekday evenings',
                'budget_eur' => '1450',
                'message' => 'I want to join the next group.',
                'privacy_consent' => '1',
                'source' => 'website',
                'utm_source' => 'google',
                'utm_medium' => 'cpc',
                'utm_campaign' => 'spring-category-b',
            ])
            ->assertRedirect(route('site.apply'))
            ->assertSessionHasNoErrors()
            ->assertSessionHas('status');

        $this->assertDatabaseHas('marketing_leads', [
            'first_name' => 'Ieva',
            'last_name' => 'Norkute',
            'email' => 'ieva@example.com',
            'status' => 'new',
            'training_program_id' => $program->id,
            'training_group_id' => $group->id,
            'preferred_format' => 'mixed',
            'preferred_language' => 'English',
            'messenger' => 'WhatsApp',
            'city' => 'Vilnius',
            'budget_cents' => 145000,
            'utm_campaign' => 'spring-category-b',
        ]);

        $lead = MarketingLead::query()
            ->where('email', 'ieva@example.com')
            ->firstOrFail();

        Notification::assertSentTo($admin, EnrollmentLeadSubmittedNotification::class);
        Notification::assertSentOnDemand(EnrollmentLeadAutoReplyNotification::class);
        $this->assertSame('B', $lead->license_category);
        $this->assertTrue($lead->is_hot);
        $this->assertSame(1, $lead->comments()->count());
        $this->assertSame(1, $lead->communications()->count());
        $this->assertSame(1, $lead->statusHistories()->count());
        $this->assertSame(1, $lead->tasks()->count());
    }

    public function test_admin_can_open_core_auto_school_sections(): void
    {
        $this->seed();

        $admin = User::query()
            ->where('email', 'admin@example.com')
            ->firstOrFail();

        $this->actingAs($admin)
            ->get(route('platform.main'))
            ->assertOk()
            ->assertSee('Auto-school operations');

        collect([
            'platform.operations.branches' => 'Branches',
            'platform.operations.instructors' => 'Instructors',
            'platform.operations.groups' => 'Training groups',
            'platform.crm.students' => 'Student CRM',
            'platform.lms.programs' => 'LMS Programs',
            'platform.schedule.lessons' => 'Schedule',
            'platform.fleet.vehicles' => 'Fleet',
            'platform.exams' => 'Exams',
            'platform.finance.payments' => 'Payments',
            'platform.documents' => 'Documents',
            'platform.marketing.campaigns' => 'Marketing campaigns',
            'platform.marketing.pipeline' => 'Sales pipeline',
            'platform.marketing.leads' => 'Marketing leads',
        ])->each(function (string $label, string $routeName) use ($admin): void {
            $this->actingAs($admin)
                ->get(route($routeName))
                ->assertOk()
                ->assertSee($label);
        });
    }

    public function test_seeded_operations_include_groups_and_marketing_pipeline(): void
    {
        $this->seed();

        $admin = User::query()
            ->where('email', 'admin@example.com')
            ->firstOrFail();

        $this->actingAs($admin)
            ->get(route('platform.operations.groups'))
            ->assertOk()
            ->assertSee('B-VNO-001')
            ->assertSee('Evening Category B Group');

        $this->actingAs($admin)
            ->get(route('platform.marketing.campaigns'))
            ->assertOk()
            ->assertSee('Spring Category B Intake')
            ->assertSee('Google Ads');

        $this->actingAs($admin)
            ->get(route('platform.marketing.leads'))
            ->assertOk()
            ->assertSee('Tomas Jankauskas')
            ->assertSee('Консультация проведена')
            ->assertSee('Telegram')
            ->assertSee('1,500.00 EUR');

        $this->actingAs($admin)
            ->get(route('platform.marketing.pipeline'))
            ->assertOk()
            ->assertSee('Sales pipeline')
            ->assertSee('Консультация проведена')
            ->assertSee('Hot')
            ->assertSee('Status conversion report')
            ->assertSee('Budget too low');

        $lead = MarketingLead::query()
            ->where('email', 'lead@drivepro.test')
            ->firstOrFail();

        $this->actingAs($admin)
            ->get(route('platform.marketing.leads.edit', $lead))
            ->assertOk()
            ->assertSee('CRM lead: Tomas Jankauskas')
            ->assertSee('Responsible manager')
            ->assertSee('Communication history')
            ->assertSee('Manager tasks')
            ->assertSee('Status history')
            ->assertSee('Attached documents')
            ->assertSee('application-document.pdf');
    }

    public function test_admin_can_move_lead_through_sales_pipeline(): void
    {
        $this->seed();

        $admin = User::query()
            ->where('email', 'admin@example.com')
            ->firstOrFail();
        $lead = MarketingLead::query()
            ->where('email', 'lead@drivepro.test')
            ->firstOrFail();

        $this->actingAs($admin)
            ->post(route('platform.marketing.pipeline', ['method' => 'moveLead']), [
                'lead_id' => $lead->id,
                'status' => LeadStatus::WaitingDocuments->value,
                'reason' => 'Student must send medical certificate.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('marketing_leads', [
            'id' => $lead->id,
            'status' => LeadStatus::WaitingDocuments->value,
        ]);
        $this->assertDatabaseHas('marketing_lead_status_histories', [
            'marketing_lead_id' => $lead->id,
            'user_id' => $admin->id,
            'from_status' => LeadStatus::ConsultationDone->value,
            'to_status' => LeadStatus::WaitingDocuments->value,
            'reason' => 'Student must send medical certificate.',
        ]);
        $this->assertDatabaseHas('marketing_lead_tasks', [
            'marketing_lead_id' => $lead->id,
            'assigned_to_user_id' => $admin->id,
            'status' => 'open',
            'priority' => 'high',
        ]);
    }
}
