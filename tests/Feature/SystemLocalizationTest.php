<?php

namespace Tests\Feature;

use App\Models\Concerns\HasTranslations;
use App\Models\Language;
use App\Models\TranslationString;
use App\Models\TranslationValue;
use App\Models\User;
use App\Services\LocaleManager;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\SystemTranslationSeeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class SystemLocalizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_languages_are_seeded(): void
    {
        $this->seed(LanguageSeeder::class);

        collect(['ru', 'en', 'lt', 'pl'])->each(fn (string $code): self => $this->assertDatabaseHas('languages', [
            'code' => $code,
            'is_active' => true,
        ]));

        $this->assertDatabaseHas('languages', [
            'code' => 'ru',
            'is_default' => true,
        ]);
    }

    public function test_only_one_language_can_be_default(): void
    {
        $this->seed(LanguageSeeder::class);

        Language::query()
            ->where('code', 'en')
            ->firstOrFail()
            ->update(['is_default' => true]);

        $this->assertSame(1, Language::query()->where('is_default', true)->count());
        $this->assertTrue(Language::query()->where('code', 'en')->firstOrFail()->is_default);
        $this->assertFalse(Language::query()->where('code', 'ru')->firstOrFail()->is_default);
    }

    public function test_translation_helper_returns_database_translation_and_clears_cache_on_update(): void
    {
        $this->seed(LanguageSeeder::class);

        $translationString = TranslationString::query()->create([
            'group' => 'tests',
            'key' => 'tests.cached_label',
            'is_system' => true,
        ]);
        $translationValue = TranslationValue::query()->create([
            'translation_string_id' => $translationString->id,
            'language_code' => 'ru',
            'value' => 'Первое значение',
            'is_approved' => true,
        ]);

        app()->setLocale('ru');

        $this->assertSame('Первое значение', tkey('tests.cached_label'));

        $translationValue->update(['value' => 'Обновленное значение']);

        $this->assertSame('Обновленное значение', tkey('tests.cached_label'));
    }

    public function test_translation_helper_falls_back_when_missing(): void
    {
        $this->seed(LanguageSeeder::class);

        app()->setLocale('ru');

        $this->assertSame('tests.missing_label', tkey('tests.missing_label'));
    }

    public function test_translations_can_be_updated(): void
    {
        $this->seed([LanguageSeeder::class, SystemTranslationSeeder::class]);

        $translationString = TranslationString::query()
            ->where('key', 'common.actions.save')
            ->firstOrFail();

        TranslationValue::query()->updateOrCreate(
            [
                'translation_string_id' => $translationString->id,
                'language_code' => 'en',
            ],
            [
                'value' => 'Store',
                'is_approved' => true,
            ],
        );

        $this->assertSame('Store', tkey('common.actions.save', locale: 'en'));
    }

    public function test_guest_can_switch_public_language(): void
    {
        $this->seed([LanguageSeeder::class, SystemTranslationSeeder::class]);

        Route::middleware(['web'])->get('/_test/public-locale', fn () => view('site.layout'));

        $this
            ->from('/_test/public-locale')
            ->post(route('locale.switch'), ['locale' => 'en'])
            ->assertRedirect('/_test/public-locale')
            ->assertSessionHas(LocaleManager::SESSION_KEY, 'en');

        $this
            ->withSession([LocaleManager::SESSION_KEY => 'en'])
            ->get('/_test/public-locale')
            ->assertOk()
            ->assertSee('<html lang="en">', false)
            ->assertSee('Knowledge base');
    }

    public function test_authenticated_user_can_save_preferred_language(): void
    {
        $this->seed([LanguageSeeder::class, SystemTranslationSeeder::class]);

        $user = User::factory()->create();

        $this
            ->actingAs($user)
            ->from(route('platform.profile'))
            ->post(route('locale.switch'), ['locale' => 'lt'])
            ->assertRedirect(route('platform.profile'))
            ->assertSessionHas(LocaleManager::SESSION_KEY, 'lt');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'preferred_locale' => 'lt',
        ]);
    }

    public function test_selected_locale_affects_tkey_and_falls_back_to_default_language(): void
    {
        $this->seed(LanguageSeeder::class);

        $localizedString = TranslationString::query()->create([
            'group' => 'tests',
            'key' => 'tests.locale_label',
            'is_system' => true,
        ]);
        $fallbackString = TranslationString::query()->create([
            'group' => 'tests',
            'key' => 'tests.fallback_label',
            'is_system' => true,
        ]);

        TranslationValue::query()->create([
            'translation_string_id' => $localizedString->id,
            'language_code' => 'ru',
            'value' => 'Русская метка',
            'is_approved' => true,
        ]);
        TranslationValue::query()->create([
            'translation_string_id' => $localizedString->id,
            'language_code' => 'en',
            'value' => 'English label',
            'is_approved' => true,
        ]);
        TranslationValue::query()->create([
            'translation_string_id' => $fallbackString->id,
            'language_code' => 'ru',
            'value' => 'Запасная метка',
            'is_approved' => true,
        ]);

        $this
            ->post(route('locale.switch'), ['locale' => 'en'])
            ->assertRedirect()
            ->assertSessionHas(LocaleManager::SESSION_KEY, 'en');

        $this->assertSame('English label', tkey('tests.locale_label'));
        $this->assertSame('Запасная метка', tkey('tests.fallback_label'));
    }

    public function test_inactive_language_cannot_be_selected(): void
    {
        $this->seed([LanguageSeeder::class, SystemTranslationSeeder::class]);

        Language::query()
            ->where('code', 'en')
            ->firstOrFail()
            ->update(['is_active' => false]);

        $user = User::factory()->create();

        Route::middleware(['web'])->get('/_test/public-locale', fn () => view('site.layout'));

        $this
            ->actingAs($user)
            ->withSession([LocaleManager::SESSION_KEY => 'ru'])
            ->from('/_test/public-locale')
            ->post(route('locale.switch'), ['locale' => 'en'])
            ->assertRedirectBackWithErrors(['locale'])
            ->assertSessionHas(LocaleManager::SESSION_KEY, 'ru');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'preferred_locale' => null,
        ]);
    }

    public function test_has_translations_trait_reads_and_writes_json_translation_fields(): void
    {
        $model = new TranslatableTestModel;

        $model->setTranslation('name', 'ru', 'Название');
        $model->setTranslation('name', 'en', 'Name');

        $this->assertSame('Название', $model->getTranslation('name', 'ru'));
        $this->assertSame('Name', $model->getTranslation('name', 'lt', 'en'));
        $this->assertSame([
            'ru' => 'Название',
            'en' => 'Name',
        ], $model->getTranslations('name'));

        $model->setTranslations('description', [
            'ru' => 'Описание',
        ]);

        $this->assertSame('Описание', $model->getTranslation('description', 'ru'));
    }

    public function test_unauthorized_user_cannot_access_translation_screens(): void
    {
        $this->seed([LanguageSeeder::class, SystemTranslationSeeder::class]);

        $user = User::factory()->create([
            'permissions' => [],
        ]);

        $this->actingAs($user)
            ->get(route('platform.system.translations'))
            ->assertForbidden();
    }

    public function test_authorized_superadmin_can_access_translation_screens(): void
    {
        $this->seed();

        $admin = User::query()
            ->where('email', 'admin@example.com')
            ->firstOrFail();

        $this->actingAs($admin)
            ->get(route('platform.system.languages'))
            ->assertOk()
            ->assertSee('Языки');

        $this->actingAs($admin)
            ->get(route('platform.system.languages.create'))
            ->assertOk()
            ->assertSee('Создать язык');

        $this->actingAs($admin)
            ->get(route('platform.system.translations'))
            ->assertOk()
            ->assertSee('Переводы');

        $translationString = TranslationString::query()
            ->where('key', 'common.actions.save')
            ->firstOrFail();

        $this->actingAs($admin)
            ->get(route('platform.system.translations.edit', $translationString))
            ->assertOk()
            ->assertSee('Редактировать перевод');
    }
}

class TranslatableTestModel extends Model
{
    use HasTranslations;

    protected $casts = [
        'name_translations' => 'array',
        'description_translations' => 'array',
    ];
}
