<?php

namespace Tests\Feature;

use App\Models\LeadSource;
use App\Models\TranslationString;
use App\Models\TranslationValue;
use App\Models\User;
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

        $this->actingAs($this->seededAdmin())
            ->get(route('platform.marketing.leads'))
            ->assertOk()
            ->assertSee('Реклама CRM');
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
