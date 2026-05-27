<?php

namespace Tests\Feature;

use App\Enums\LeadStatus;
use App\Enums\LeadTaskStatus;
use App\Models\Branch;
use App\Models\LeadLostReason;
use App\Models\LeadTag;
use App\Models\MarketingLead;
use App\Models\MarketingMessageTemplate;
use App\Models\PricingPackage;
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
            ->assertSee('Категория B в Вильнюсе')
            ->assertSee('Заявки сразу попадают в CRM')
            ->assertSee(tkey('website.home.programs.title', [], 'ru'))
            ->assertSee(tkey('website.home.groups.title', [], 'ru'));
    }

    public function test_public_auto_school_pages_render_seeded_catalog(): void
    {
        $this->seed();

        $program = TrainingProgram::query()
            ->where('slug', 'category-b-manual')
            ->firstOrFail();

        collect([
            route('site.courses.show', $program) => $program->displayTitle(),
            route('site.categories.show', $program) => $program->displayTitle(),
            route('site.apply') => tkey('website.apply.title', [], 'ru'),
            route('site.prices') => tkey('website.prices.title', [], 'ru'),
            route('site.instructors') => 'Инструкторы автошколы',
            route('site.fleet') => 'Автопарк',
            route('site.reviews') => 'Отзывы учеников',
            route('site.blog.index') => 'Блог и база знаний',
            route('site.contacts') => tkey('website.contacts.title', [], 'ru'),
            route('site.thanks') => tkey('website.thanks.title', [], 'ru'),
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
            ->assertRedirect(route('site.thanks'))
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
            'form_name' => 'enrollment',
            'locale' => 'ru',
        ]);

        $lead = MarketingLead::query()
            ->where('email', 'ieva@example.com')
            ->firstOrFail();

        Notification::assertSentTo($admin, EnrollmentLeadSubmittedNotification::class);
        Notification::assertSentOnDemand(EnrollmentLeadAutoReplyNotification::class);
        $this->assertSame('B', $lead->license_category);
        $this->assertNotNull($lead->uuid);
        $this->assertNotNull($lead->landing_page);
        $this->assertSame(route('site.apply'), $lead->form_page);
        $this->assertTrue($lead->is_hot);
        $this->assertSame(1, $lead->comments()->count());
        $this->assertSame(1, $lead->communications()->count());
        $this->assertSame(1, $lead->statusHistories()->count());
        $this->assertSame(1, $lead->tasks()->count());
    }

    public function test_callback_form_creates_callback_lead(): void
    {
        Notification::fake();
        $this->seed();

        $this->from(route('site.contacts'))
            ->post(route('site.callback.store'), [
                'first_name' => 'Laura',
                'phone' => '+370 600 77777',
                'preferred_time' => 'Tomorrow morning',
                'message' => 'Please call me about courses.',
                'privacy_consent' => '1',
                'source' => 'callback',
                'form_name' => 'callback',
                'utm_source' => 'google',
            ])
            ->assertRedirect(route('site.thanks'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('marketing_leads', [
            'first_name' => 'Laura',
            'phone' => '+37060077777',
            'source' => 'callback',
            'status' => 'new',
            'form_name' => 'callback',
            'utm_source' => 'google',
            'locale' => 'ru',
        ]);

        $lead = MarketingLead::query()
            ->where('phone', '+37060077777')
            ->firstOrFail();

        $this->assertNotNull($lead->uuid);
        $this->assertSame(1, $lead->comments()->count());
        $this->assertSame(1, $lead->tasks()->count());
    }

    public function test_public_callback_marks_possible_duplicate_by_normalized_phone(): void
    {
        Notification::fake();
        $this->seed();

        $original = MarketingLead::factory()->create([
            'first_name' => 'Original',
            'last_name' => 'Client',
            'phone' => '+371 299 88776',
            'email' => 'original@example.com',
            'status' => LeadStatus::New,
        ]);

        $this->from(route('site.contacts'))
            ->post(route('site.callback.store'), [
                'first_name' => 'Duplicate',
                'phone' => '37129988776',
                'preferred_time' => 'Today',
                'privacy_consent' => '1',
                'source' => 'callback',
                'form_name' => 'callback',
            ])
            ->assertRedirect(route('site.thanks'))
            ->assertSessionHasNoErrors();

        $duplicate = MarketingLead::query()
            ->where('first_name', 'Duplicate')
            ->firstOrFail();

        $this->assertSame($original->id, $duplicate->duplicate_of_id);
        $this->assertSame('+37129988776', $duplicate->normalized_phone);
        $this->assertDatabaseHas('marketing_lead_activities', [
            'marketing_lead_id' => $duplicate->id,
            'type' => 'marked_duplicate',
            'new_value' => (string) $original->id,
        ]);
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
            'platform.website.courses' => tkey('website.admin.courses.title'),
            'platform.website.pricing' => tkey('website.admin.pricing.title'),
            'platform.website.branches' => tkey('website.admin.branches.title'),
            'platform.website.groups' => tkey('website.admin.groups.title'),
            'platform.website.leads' => tkey('website.admin.leads.title'),
            'platform.website.settings' => tkey('website.admin.settings.title'),
            'platform.operations.branches' => tkey('website.admin.branches.title'),
            'platform.operations.instructors' => 'Instructors',
            'platform.operations.groups' => tkey('website.admin.groups.title'),
            'platform.crm.students' => 'Student CRM',
            'platform.lms.programs' => tkey('website.admin.courses.title'),
            'platform.schedule.lessons' => 'Schedule',
            'platform.fleet.vehicles' => 'Fleet',
            'platform.exams' => 'Exams',
            'platform.finance.payments' => 'Payments',
            'platform.documents' => 'Documents',
            'platform.marketing.campaigns' => 'Marketing campaigns',
            'platform.marketing.pipeline' => 'Воронка продаж',
            'platform.marketing.leads' => 'Лиды',
            'platform.crm.tasks' => 'Задачи',
            'platform.marketing.templates' => 'Шаблоны сообщений',
        ])->each(function (string $label, string $routeName) use ($admin): void {
            $this->actingAs($admin)
                ->get(route($routeName))
                ->assertOk()
                ->assertSee($label);
        });
    }

    public function test_admin_can_open_catalog_create_and_edit_screens(): void
    {
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
        $package = PricingPackage::query()
            ->where('slug', 'category-b-premium')
            ->firstOrFail();

        collect([
            route('platform.website.courses.create') => tkey('website.admin.courses.create_title', [], 'ru'),
            route('platform.website.courses.edit', $program) => tkey('website.admin.courses.edit_title', [], 'ru'),
            route('platform.website.pricing.create') => tkey('website.admin.pricing.create_title', [], 'ru'),
            route('platform.website.pricing.edit', $package) => tkey('website.admin.pricing.edit_title', [], 'ru'),
            route('platform.website.branches.create') => tkey('website.admin.branches.create_title', [], 'ru'),
            route('platform.website.branches.edit', $branch) => tkey('website.admin.branches.edit_title', [], 'ru'),
            route('platform.website.groups.create') => tkey('website.admin.groups.create_title', [], 'ru'),
            route('platform.website.groups.edit', $group) => tkey('website.admin.groups.edit_title', [], 'ru'),
            route('platform.marketing.leads.create') => tkey('crm.leads.create_title', [], 'ru'),
        ])->each(function (string $title, string $url) use ($admin): void {
            $this->actingAs($admin)
                ->get($url)
                ->assertOk()
                ->assertSee($title);
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
            ->assertSee('Вечерняя группа категории B');

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

    public function test_admin_can_create_manual_lead_from_crm_card(): void
    {
        $this->seed();

        $admin = User::query()
            ->where('email', 'admin@example.com')
            ->firstOrFail();
        $tag = LeadTag::query()
            ->where('slug', 'hot_lead')
            ->firstOrFail();

        $this->actingAs($admin)
            ->post(route('platform.marketing.leads.create', ['method' => 'save']), [
                'lead' => [
                    'first_name' => 'Manual',
                    'last_name' => 'Lead',
                    'email' => 'manual-lead@example.com',
                    'source' => 'phone',
                    'responsible_manager_id' => $admin->id,
                    'priority' => 'urgent',
                    'lead_score' => 85,
                    'message' => 'Client called the office.',
                    'internal_comment' => 'Prepare documents checklist.',
                    'consent_accepted' => '1',
                    'consent_text_version' => 'manual-v1',
                    'tag_ids' => [$tag->id],
                ],
                'lead_status' => LeadStatus::New->value,
                'lead_budget_eur' => '950',
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $lead = MarketingLead::query()
            ->where('email', 'manual-lead@example.com')
            ->firstOrFail();

        $this->assertSame(LeadStatus::New, $lead->status);
        $this->assertSame($admin->id, $lead->responsible_manager_id);
        $this->assertSame('urgent', $lead->priority);
        $this->assertSame(85, $lead->lead_score);
        $this->assertSame(95000, $lead->budget_cents);
        $this->assertTrue($lead->consent_accepted);
        $this->assertTrue($lead->tags()->whereKey($tag->id)->exists());
        $this->assertSame(1, $lead->statusHistories()->count());
        $this->assertDatabaseHas('marketing_lead_activities', [
            'marketing_lead_id' => $lead->id,
            'type' => 'created',
        ]);

        $export = $this->actingAs($admin)
            ->post(route('platform.marketing.leads', [
                'method' => 'export',
                'search' => 'manual-lead@example.com',
            ]))
            ->assertOk();

        $this->assertStringContainsString('Manual Lead', $export->streamedContent());
    }

    public function test_admin_can_create_and_complete_lead_task_from_crm(): void
    {
        $this->seed();

        $admin = User::query()
            ->where('email', 'admin@example.com')
            ->firstOrFail();
        $lead = MarketingLead::factory()->create([
            'first_name' => 'Task',
            'last_name' => 'Client',
            'responsible_manager_id' => $admin->id,
            'status' => LeadStatus::Contacted,
        ]);

        $this->actingAs($admin)
            ->post(route('platform.marketing.leads.edit', ['lead' => $lead, 'method' => 'createTask']), [
                'task' => [
                    'title' => 'Call about documents',
                    'notes' => 'Ask for medical certificate.',
                    'assigned_to_user_id' => $admin->id,
                    'priority' => 'high',
                    'due_at' => now()->subHour()->format('Y-m-d\TH:i'),
                ],
            ])
            ->assertRedirect(route('platform.marketing.leads.edit', $lead))
            ->assertSessionHasNoErrors();

        $task = $lead->tasks()
            ->where('title', 'Call about documents')
            ->firstOrFail();

        $this->actingAs($admin)
            ->get(route('platform.crm.tasks', ['segment' => 'overdue']))
            ->assertOk()
            ->assertSee('Call about documents')
            ->assertSee(tkey('crm.tasks.segments.overdue'));

        $this->actingAs($admin)
            ->post(route('platform.crm.tasks', ['method' => 'complete']), [
                'task' => $task->id,
            ])
            ->assertRedirect(route('platform.crm.tasks'));

        $task->refresh();
        $this->assertSame(LeadTaskStatus::Done, $task->status);
        $this->assertNotNull($task->completed_at);
        $this->assertDatabaseHas('marketing_lead_activities', [
            'marketing_lead_id' => $lead->id,
            'type' => 'task_completed',
        ]);
    }

    public function test_admin_can_mark_leads_lost_duplicate_spam_and_prepare_enrollment(): void
    {
        $this->seed();

        $admin = User::query()
            ->where('email', 'admin@example.com')
            ->firstOrFail();

        $lostReason = LeadLostReason::query()
            ->where('code', 'price')
            ->firstOrFail();
        $lost = MarketingLead::factory()->create(['status' => LeadStatus::New]);

        $this->actingAs($admin)
            ->post(route('platform.marketing.leads.edit', ['lead' => $lost, 'method' => 'markLost']), [
                'lost' => [
                    'reason' => $lostReason->code,
                    'comment' => 'Client found a cheaper course.',
                ],
            ])
            ->assertRedirect(route('platform.marketing.leads.edit', $lost))
            ->assertSessionHasNoErrors();

        $lost->refresh();
        $this->assertSame(LeadStatus::Lost, $lost->status);
        $this->assertSame($lostReason->code, $lost->lost_reason_code);
        $this->assertNotNull($lost->closed_at);

        $original = MarketingLead::factory()->create(['status' => LeadStatus::Contacted]);
        $duplicate = MarketingLead::factory()->create(['status' => LeadStatus::New]);

        $this->actingAs($admin)
            ->post(route('platform.marketing.leads.edit', ['lead' => $duplicate, 'method' => 'markDuplicate']), [
                'duplicate' => [
                    'original_id' => $duplicate->id,
                ],
            ])
            ->assertSessionHasErrors('duplicate.original_id');

        $this->actingAs($admin)
            ->post(route('platform.marketing.leads.edit', ['lead' => $duplicate, 'method' => 'markDuplicate']), [
                'duplicate' => [
                    'original_id' => $original->id,
                    'comment' => 'Same phone from another form.',
                ],
            ])
            ->assertRedirect(route('platform.marketing.leads.edit', $duplicate))
            ->assertSessionHasNoErrors();

        $duplicate->refresh();
        $this->assertSame(LeadStatus::Duplicate, $duplicate->status);
        $this->assertSame($original->id, $duplicate->duplicate_of_id);
        $this->assertNotNull($duplicate->closed_at);

        $spam = MarketingLead::factory()->create(['status' => LeadStatus::New]);

        $this->actingAs($admin)
            ->post(route('platform.marketing.leads.edit', ['lead' => $spam, 'method' => 'markSpam']))
            ->assertRedirect(route('platform.marketing.leads.edit', $spam));

        $spam->refresh();
        $this->assertSame(LeadStatus::Spam, $spam->status);
        $this->assertNotNull($spam->closed_at);

        $ready = MarketingLead::factory()->create(['status' => LeadStatus::WaitingPayment]);

        $this->actingAs($admin)
            ->post(route('platform.marketing.leads.edit', ['lead' => $ready, 'method' => 'prepareEnrollment']))
            ->assertRedirect(route('platform.marketing.leads.edit', $ready));

        $ready->refresh();
        $this->assertSame(LeadStatus::ReadyToEnroll, $ready->status);
        $this->assertNull($ready->closed_at);
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
