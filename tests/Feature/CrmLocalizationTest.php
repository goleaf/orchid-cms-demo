<?php

namespace Tests\Feature;

use App\Enums\LeadStatus as LeadStatusEnum;
use App\Models\LeadSource;
use App\Models\LeadStatus as LeadStatusDictionary;
use App\Models\MarketingLead;
use App\Models\TranslationString;
use App\Models\TranslationValue;
use App\Models\User;
use App\Notifications\EnrollmentLeadAutoReplyNotification;
use App\Notifications\EnrollmentLeadSubmittedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrmLocalizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_crm_screen_renders_translated_labels_for_default_locale(): void
    {
        $this->seed();
        app()->setLocale('ru');

        $admin = $this->seededAdmin();

        $this->actingAs($admin)
            ->get(route('platform.marketing.leads'))
            ->assertOk()
            ->assertSee('Лиды')
            ->assertSee('Полное имя')
            ->assertSee('Менеджер')
            ->assertSee('Следующий контакт')
            ->assertSee('Открыть');

        $this->actingAs($admin)
            ->get(route('platform.marketing.pipeline'))
            ->assertOk()
            ->assertSee('Воронка продаж')
            ->assertSee('Отчёт по статусам')
            ->assertSee('Горячий');
    }

    public function test_changing_database_translation_changes_displayed_crm_label(): void
    {
        $this->seed();
        app()->setLocale('ru');

        $translationString = TranslationString::query()
            ->where('key', 'crm.leads.columns.full_name')
            ->firstOrFail();

        TranslationValue::query()
            ->where('translation_string_id', $translationString->id)
            ->where('language_code', 'ru')
            ->firstOrFail()
            ->update(['value' => 'Клиент CRM']);

        $this->actingAs($this->seededAdmin())
            ->get(route('platform.marketing.leads'))
            ->assertOk()
            ->assertSee('Клиент CRM');
    }

    public function test_missing_crm_translation_falls_back_safely(): void
    {
        $this->seed();
        app()->setLocale('ru');

        TranslationString::query()
            ->where('key', 'crm.leads.columns.phone')
            ->firstOrFail()
            ->delete();

        $this->actingAs($this->seededAdmin())
            ->get(route('platform.marketing.leads'))
            ->assertOk()
            ->assertSee('crm.leads.columns.phone');
    }

    public function test_dictionary_translated_names_display_in_crm_screens(): void
    {
        $this->seed();
        app()->setLocale('ru');

        LeadSource::query()
            ->where('code', 'google_ads')
            ->firstOrFail()
            ->update([
                'name' => 'Реклама CRM',
                'name_translations' => [
                    'ru' => 'Реклама CRM',
                    'en' => 'CRM ads',
                    'lt' => 'CRM reklama',
                    'pl' => 'Reklama CRM',
                ],
            ]);

        LeadStatusDictionary::query()
            ->where('code', LeadStatusEnum::New->value)
            ->firstOrFail()
            ->update([
                'name' => 'Новая заявка CRM',
                'name_translations' => [
                    'ru' => 'Новая заявка CRM',
                    'en' => 'CRM new lead',
                    'lt' => 'CRM nauja uzklausa',
                    'pl' => 'Nowy lead CRM',
                ],
            ]);

        MarketingLead::factory()->create([
            'source' => 'google_ads',
            'status' => LeadStatusEnum::New,
            'first_name' => 'CRM',
            'last_name' => 'Dictionary',
        ]);

        $this->actingAs($this->seededAdmin())
            ->get(route('platform.marketing.leads'))
            ->assertOk()
            ->assertSee('Реклама CRM')
            ->assertSee('Новая заявка CRM');
    }

    public function test_enrollment_lead_notifications_use_translation_keys(): void
    {
        $this->seed();
        app()->setLocale('ru');

        $lead = MarketingLead::factory()->create([
            'first_name' => 'Tomas',
            'last_name' => 'Jankauskas',
            'email' => null,
            'phone' => null,
            'preferred_time' => null,
            'preferred_format' => null,
        ]);

        $admin = $this->seededAdmin();
        $admin->forceFill(['preferred_locale' => 'ru'])->save();

        $submittedMail = (new EnrollmentLeadSubmittedNotification($lead))->toMail($admin);

        $this->assertSame('Новая заявка в автошколу', $submittedMail->subject);
        $this->assertContains('Tomas Jankauskas отправил онлайн-заявку на обучение.', $submittedMail->introLines);
        $this->assertContains('Контакт: не указано', $submittedMail->introLines);
        $this->assertContains('Предпочитаемое время: не указано', $submittedMail->introLines);
        $this->assertSame('Открыть лиды', $submittedMail->actionText);

        $autoReplyMail = (new EnrollmentLeadAutoReplyNotification($lead))->toMail((object) []);

        $this->assertSame('Заявка DrivePro Academy получена', $autoReplyMail->subject);
        $this->assertSame('Здравствуйте, Tomas', $autoReplyMail->greeting);
        $this->assertContains('Формат обучения: не выбран', $autoReplyMail->introLines);
        $this->assertSame('Открыть сайт', $autoReplyMail->actionText);
    }

    public function test_superadmin_can_edit_crm_dictionary_translations(): void
    {
        $this->seed();
        app()->setLocale('ru');

        $source = LeadSource::query()
            ->where('code', 'google_ads')
            ->firstOrFail();

        $this->actingAs($this->seededAdmin())
            ->get(route('platform.crm.dictionaries', 'sources'))
            ->assertOk()
            ->assertSee('Источники лидов')
            ->assertSee('Google Ads');

        $this->actingAs($this->seededAdmin())
            ->post(route('platform.crm.dictionaries.edit', [
                'dictionary' => 'sources',
                'record' => $source->id,
                'method' => 'save',
            ]), [
                'item' => [
                    'code' => 'google_ads',
                    'name' => 'Реклама Google',
                    'is_active' => '1',
                    'sort_order' => 60,
                ],
                'name_translations' => [
                    'ru' => 'Реклама Google',
                    'en' => 'Google advertising',
                    'lt' => 'Google reklama',
                    'pl' => 'Reklama Google',
                ],
            ])
            ->assertRedirect(route('platform.crm.dictionaries', 'sources'))
            ->assertSessionHasNoErrors();

        $source->refresh();

        $this->assertSame('Реклама Google', $source->getTranslation('name', 'ru'));
        $this->assertSame('Google advertising', $source->getTranslation('name', 'en'));
    }

    private function seededAdmin(): User
    {
        return User::query()
            ->where('email', 'admin@example.com')
            ->firstOrFail();
    }
}
