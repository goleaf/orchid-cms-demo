<?php

namespace Tests\Feature;

use App\Models\Concerns\HasTranslations;
use App\Orchid\Support\TranslatableFields;
use App\Services\TranslatableContentManager;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\SystemTranslationSeeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Orchid\Screen\Repository;
use Tests\TestCase;

class TranslatableFieldsTest extends TestCase
{
    use RefreshDatabase;

    public function test_translations_are_saved_from_request_data(): void
    {
        $this->seed(LanguageSeeder::class);

        $request = Request::create('/admin/test', 'POST', [
            'name_translations' => [
                'ru' => 'Категория B',
                'en' => 'Category B',
                'lt' => 'B kategorija',
                'xx' => 'Ignored locale',
            ],
            'description_translations' => [
                'ru' => 'Описание',
                'en' => null,
            ],
            TranslatableContentManager::COPY_KEY => [
                'description' => [
                    'en' => '1',
                ],
            ],
        ]);

        $data = app(TranslatableContentManager::class)->extract($request, [
            'name',
            'description',
        ]);

        $model = new TranslatableFieldsTestModel;
        $model->forceFill($data);

        $this->assertSame([
            'ru' => 'Категория B',
            'en' => 'Category B',
            'lt' => 'B kategorija',
            'pl' => null,
        ], $model->getTranslations('name'));
        $this->assertSame('Описание', $model->getTranslation('description', 'en', 'ru'));
        $this->assertArrayNotHasKey('xx', $model->getTranslations('name'));
    }

    public function test_default_locale_value_is_displayed_in_orchid_layout(): void
    {
        $this->seed([LanguageSeeder::class, SystemTranslationSeeder::class]);
        app()->setLocale('en');

        $html = TranslatableFields::input('name', 'crm.dictionaries.fields.name_translations')
            ->build(new Repository([
                'name_translations' => [
                    'ru' => 'Категория B',
                ],
            ]))
            ->render();

        $this->assertStringContainsString('name_translations[ru]', $html);
        $this->assertStringContainsString('Категория B', $html);
        $this->assertStringContainsString('Default', $html);
        $this->assertStringContainsString('Translation is missing; the default language will be used.', $html);
        $this->assertStringContainsString('Copy from default language', $html);
    }

    public function test_seo_and_quill_fields_use_translation_payload_names(): void
    {
        $this->seed([LanguageSeeder::class, SystemTranslationSeeder::class]);
        app()->setLocale('en');

        $seoHtml = TranslatableFields::seo()
            ->build(new Repository([
                'seo_title_translations' => [
                    'ru' => 'SEO заголовок',
                ],
            ]))
            ->render();
        $quillHtml = TranslatableFields::quill('content', 'translatable.seo.fields.seo_description')
            ->build(new Repository([
                'content_translations' => [
                    'ru' => '<p>Описание</p>',
                ],
            ]))
            ->render();

        $this->assertStringContainsString('seo_title_translations[ru]', $seoHtml);
        $this->assertStringContainsString('seo_description_translations[ru]', $seoHtml);
        $this->assertStringContainsString('seo_keywords_translations[ru]', $seoHtml);
        $this->assertStringContainsString('og_title_translations[ru]', $seoHtml);
        $this->assertStringContainsString('og_description_translations[ru]', $seoHtml);
        $this->assertStringContainsString('content_translations[ru]', $quillHtml);
    }

    public function test_fallback_locale_works_for_translatable_model_content(): void
    {
        $this->seed(LanguageSeeder::class);

        $model = new TranslatableFieldsTestModel;
        $model->setTranslations('name', [
            'ru' => 'Категория B',
            'en' => null,
        ]);

        $this->assertSame('Категория B', $model->getTranslation('name', 'en', 'ru'));
    }

    public function test_missing_translations_are_detected(): void
    {
        $this->seed(LanguageSeeder::class);

        $missing = app(TranslatableContentManager::class)->missingTranslations([
            'ru' => 'Категория B',
            'en' => '',
            'lt' => null,
        ]);

        $this->assertSame(['en', 'lt', 'pl'], $missing);
    }
}

class TranslatableFieldsTestModel extends Model
{
    use HasTranslations;

    protected $casts = [
        'name_translations' => 'array',
        'description_translations' => 'array',
    ];
}
