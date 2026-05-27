<?php

namespace Tests\Feature;

use App\Enums\LeadStatus;
use App\Models\Branch;
use App\Models\MarketingLead;
use App\Models\MarketingMessageTemplate;
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
            'platform.marketing.pipeline' => 'Воронка продаж',
            'platform.marketing.leads' => 'Лиды',
            'platform.marketing.templates' => 'Шаблоны сообщений',
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
            ->assertSee('Воронка продаж')
            ->assertSee('Консультация проведена')
            ->assertSee('Горячий')
            ->assertSee('Отчёт по статусам')
            ->assertSee('Бюджет слишком низкий');

        $lead = MarketingLead::query()
            ->where('email', 'lead@drivepro.test')
            ->firstOrFail();

        $this->actingAs($admin)
            ->get(route('platform.marketing.leads.edit', $lead))
            ->assertOk()
            ->assertSee('CRM лид: Tomas Jankauskas')
            ->assertSee('Менеджер')
            ->assertSee('История коммуникаций')
            ->assertSee('Шаблон сообщения')
            ->assertSee('URL записи звонка')
            ->assertSee('Consultation call')
            ->assertSee('Клиент ответил')
            ->assertSee('telephony.drivepro.test')
            ->assertSee('Задачи')
            ->assertSee('История статусов')
            ->assertSee('Прикрепленные документы')
            ->assertSee('application-document.pdf');
    }

    public function test_admin_can_manage_marketing_message_templates(): void
    {
        $this->seed();

        $admin = User::query()
            ->where('email', 'admin@example.com')
            ->firstOrFail();
        $template = MarketingMessageTemplate::query()
            ->where('name', 'SMS callback reminder')
            ->firstOrFail();

        $this->actingAs($admin)
            ->get(route('platform.marketing.templates'))
            ->assertOk()
            ->assertSee('Шаблоны сообщений')
            ->assertSee('SMS callback reminder')
            ->assertSee('SMS');

        $this->actingAs($admin)
            ->get(route('platform.marketing.templates.edit', $template))
            ->assertOk()
            ->assertSee('Редактировать шаблон сообщения')
            ->assertSee('SMS callback reminder');

        $this->actingAs($admin)
            ->post(route('platform.marketing.templates.edit', ['messageTemplate' => $template, 'method' => 'save']), [
                'template' => [
                    'name' => 'SMS callback reminder',
                    'channel' => 'sms',
                    'subject' => null,
                    'body' => 'Updated callback copy for the CRM manager.',
                    'is_active' => '1',
                    'sort_order' => 25,
                ],
            ])
            ->assertRedirect(route('platform.marketing.templates'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('marketing_message_templates', [
            'id' => $template->id,
            'body' => 'Updated callback copy for the CRM manager.',
            'sort_order' => 25,
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('platform.marketing.templates.create'))
            ->assertOk()
            ->assertSee('Создать шаблон сообщения');

        $this->actingAs($admin)
            ->post(route('platform.marketing.templates.create', ['method' => 'save']), [
                'template' => [
                    'name' => 'WhatsApp document reminder',
                    'channel' => 'whatsapp',
                    'subject' => 'Documents',
                    'body' => 'Please send the missing documents before the lesson.',
                    'is_active' => '1',
                    'sort_order' => 40,
                ],
            ])
            ->assertRedirect(route('platform.marketing.templates'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('marketing_message_templates', [
            'name' => 'WhatsApp document reminder',
            'channel' => 'whatsapp',
            'is_active' => true,
            'sort_order' => 40,
        ]);
    }

    public function test_admin_can_log_multichannel_lead_communication_with_callback(): void
    {
        $this->seed();

        $admin = User::query()
            ->where('email', 'admin@example.com')
            ->firstOrFail();
        $lead = MarketingLead::query()
            ->where('email', 'lead@drivepro.test')
            ->firstOrFail();
        $template = MarketingMessageTemplate::query()
            ->where('name', 'SMS callback reminder')
            ->firstOrFail();

        $this->actingAs($admin)
            ->post(route('platform.marketing.leads.edit', ['lead' => $lead, 'method' => 'addCommunication']), [
                'communication' => [
                    'channel' => 'sms',
                    'template_id' => $template->id,
                    'direction' => 'outbound',
                    'client_replied' => '1',
                    'callback_required' => '1',
                    'callback_required_at' => now()->addHours(4)->format('Y-m-d\TH:i'),
                    'call_recording_reference' => 'SMS-GATEWAY-42',
                ],
            ])
            ->assertRedirect(route('platform.marketing.leads.edit', $lead));

        $communication = $lead->communications()
            ->where('channel', 'sms')
            ->where('call_recording_reference', 'SMS-GATEWAY-42')
            ->firstOrFail();

        $this->assertSame($template->id, $communication->marketing_message_template_id);
        $this->assertSame($template->body, $communication->body);
        $this->assertNotNull($communication->client_replied_at);
        $this->assertNotNull($communication->callback_required_at);
        $this->assertDatabaseHas('marketing_lead_tasks', [
            'marketing_lead_id' => $lead->id,
            'assigned_to_user_id' => $admin->id,
            'title' => 'Перезвонить: Tomas Jankauskas',
            'status' => 'open',
            'priority' => 'high',
        ]);
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
