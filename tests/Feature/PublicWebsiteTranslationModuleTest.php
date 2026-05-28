<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Course;
use App\Rules\SeoMetadataRule;
use App\Rules\ValidSlugRule;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\WebsiteTranslationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class PublicWebsiteTranslationModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_website_translation_keys_resolve_for_active_languages(): void
    {
        $this->seedWebsiteTranslations();

        foreach ($this->requiredKeys() as $key) {
            foreach (['ru', 'en', 'lt', 'pl'] as $locale) {
                $this->assertNotSame($key, tkey($key, [], $locale), $key.' '.$locale);
                $this->assertNotSame('', tkey($key, [], $locale), $key.' '.$locale);
            }
        }
    }

    public function test_validation_messages_are_translated(): void
    {
        $this->seedWebsiteTranslations();
        Course::factory()->create(['slug' => 'duplicate-slug']);

        $seoTitle = Validator::make(
            ['seo_title' => str_repeat('A', 71)],
            ['seo_title' => [new SeoMetadataRule(70)]],
        );
        $seoDescription = Validator::make(
            ['seo_description' => str_repeat('A', 181)],
            ['seo_description' => [new SeoMetadataRule(180)]],
        );
        $duplicateSlug = Validator::make(
            ['slug' => 'duplicate-slug'],
            ['slug' => [new ValidSlugRule(Course::class)]],
        );

        $this->assertTrue($seoTitle->fails());
        $this->assertTrue($seoDescription->fails());
        $this->assertTrue($duplicateSlug->fails());
        $this->assertSame(tkey('website.validation.seo_title_too_long'), $seoTitle->errors()->first('seo_title'));
        $this->assertSame(tkey('website.validation.seo_description_too_long'), $seoDescription->errors()->first('seo_description'));
        $this->assertSame(tkey('website.validation.slug_already_exists'), $duplicateSlug->errors()->first('slug'));
    }

    public function test_public_form_errors_use_translated_custom_messages(): void
    {
        $this->seedWebsiteTranslations();
        $course = Course::factory()->create([
            'is_active' => true,
            'is_visible_on_site' => true,
        ]);
        $branch = Branch::factory()->create([
            'is_active' => true,
            'is_visible_on_site' => true,
        ]);

        $response = $this
            ->withSession(['locale' => 'lt'])
            ->from(route('site.apply'))
            ->post(route('site.apply.store'), [
                'training_program_id' => $course->id,
                'branch_id' => $branch->id,
                'first_name' => 'Ieva',
                'email' => 'not-an-email',
                'phone' => '+370 600 44444',
                'preferred_format' => 'mixed',
                'preferred_language' => 'lt',
                'privacy_consent' => '1',
            ]);

        $response->assertSessionHasErrors('email');

        $message = session('errors')->first('email');

        $this->assertSame(tkey('website.validation.email_invalid', [], 'lt'), $message);
    }

    public function test_translated_content_displays_current_locale(): void
    {
        $this->seedWebsiteTranslations();
        $course = Course::factory()->create([
            'slug' => 'translated-locale-course',
            'title' => 'RU course',
            'title_translations' => [
                'ru' => 'RU course',
                'en' => 'EN course',
                'lt' => 'LT mokymo kursas',
                'pl' => 'PL course',
            ],
            'name_translations' => [
                'ru' => 'RU course',
                'en' => 'EN course',
                'lt' => 'LT mokymo kursas',
                'pl' => 'PL course',
            ],
            'description_translations' => [
                'ru' => 'RU description',
                'lt' => 'LT aprasymas',
            ],
            'is_active' => true,
            'is_visible_on_site' => true,
        ]);

        $this
            ->withSession(['locale' => 'lt'])
            ->get(route('site.courses.show', $course))
            ->assertOk()
            ->assertSee('LT mokymo kursas')
            ->assertSee('LT aprasymas');
    }

    public function test_missing_translated_content_falls_back_to_default_locale(): void
    {
        $this->seedWebsiteTranslations();
        $course = Course::factory()->create([
            'slug' => 'fallback-locale-course',
            'title' => 'RU fallback course',
            'title_translations' => [
                'ru' => 'RU fallback course',
            ],
            'name_translations' => [
                'ru' => 'RU fallback course',
            ],
            'description_translations' => [
                'ru' => 'RU fallback description',
            ],
            'is_active' => true,
            'is_visible_on_site' => true,
        ]);

        $this
            ->withSession(['locale' => 'lt'])
            ->get(route('site.courses.show', $course))
            ->assertOk()
            ->assertSee('RU fallback course')
            ->assertSee('RU fallback description');
    }

    public function test_seo_translations_fall_back_safely(): void
    {
        $this->seedWebsiteTranslations();
        $course = Course::factory()->create([
            'title_translations' => [
                'ru' => 'RU SEO course',
                'lt' => 'LT SEO course',
            ],
            'seo_title_translations' => [
                'ru' => 'RU SEO title',
            ],
            'seo_description_translations' => [
                'ru' => 'RU SEO description',
            ],
        ]);

        $this->assertSame('RU SEO title', $course->displaySeoTitle('lt'));
        $this->assertSame('RU SEO description', $course->displaySeoDescription('lt'));
    }

    private function seedWebsiteTranslations(): void
    {
        $this->seed(LanguageSeeder::class);
        $this->seed(WebsiteTranslationSeeder::class);
    }

    /**
     * @return array<int, string>
     */
    private function requiredKeys(): array
    {
        return [
            'menu.website',
            'menu.website.pages',
            'menu.website.courses',
            'menu.website.course_categories',
            'menu.website.pricing',
            'menu.website.branches',
            'menu.website.groups',
            'menu.website.faq',
            'menu.website.testimonials',
            'menu.website.leads',
            'menu.website.settings',
            'menu.website.seo',
            'website.nav.home',
            'website.nav.courses',
            'website.nav.pricing',
            'website.nav.branches',
            'website.nav.contacts',
            'website.nav.faq',
            'website.nav.apply',
            'website.actions.apply',
            'website.actions.callback',
            'website.actions.contact',
            'website.actions.learn_more',
            'website.actions.view_course',
            'website.actions.choose_group',
            'website.actions.send',
            'website.actions.submit',
            'website.actions.cancel',
            'website.actions.back',
            'website.actions.open',
            'website.actions.show_more',
            'website.actions.show_less',
            'website.home.title',
            'website.home.subtitle',
            'website.home.hero_cta',
            'website.home.callback_cta',
            'website.home.courses_title',
            'website.home.courses_subtitle',
            'website.home.benefits_title',
            'website.home.groups_title',
            'website.home.pricing_title',
            'website.home.testimonials_title',
            'website.home.contacts_title',
            'website.courses.title',
            'website.courses.empty.no_courses',
            'website.courses.fields.name',
            'website.courses.fields.short_description',
            'website.courses.fields.description',
            'website.courses.fields.price',
            'website.courses.fields.old_price',
            'website.courses.fields.duration',
            'website.courses.fields.theory_hours',
            'website.courses.fields.practice_hours',
            'website.courses.fields.format',
            'website.courses.fields.includes',
            'website.courses.fields.excludes',
            'website.courses.fields.requirements',
            'website.courses.sections.overview',
            'website.courses.sections.program',
            'website.courses.sections.price',
            'website.courses.sections.groups',
            'website.courses.sections.documents',
            'website.courses.sections.faq',
            'website.courses.sections.apply',
            'website.courses.formats.offline',
            'website.courses.formats.online',
            'website.courses.formats.hybrid',
            'website.courses.formats.individual',
            'website.courses.formats.group',
            'website.courses.formats.mixed',
            'website.transmissions.manual',
            'website.transmissions.automatic',
            'website.pricing.title',
            'website.pricing.subtitle',
            'website.pricing.fields.package',
            'website.pricing.fields.price',
            'website.pricing.fields.features',
            'website.pricing.fields.course',
            'website.pricing.fields.theory_hours',
            'website.pricing.fields.practice_hours',
            'website.pricing.empty.no_packages',
            'website.pricing.packages.standard',
            'website.pricing.packages.premium',
            'website.pricing.packages.intensive',
            'website.pricing.packages.extra_lessons',
            'website.prices.filters.kicker',
            'website.prices.filters.title',
            'website.prices.filters.subtitle',
            'website.prices.filters.all_courses',
            'website.prices.filters.all_formats',
            'website.prices.filters.all_durations',
            'website.prices.filters.duration_weeks',
            'website.prices.filters.price_min',
            'website.prices.filters.price_max',
            'website.prices.filters.active',
            'website.branches.title',
            'website.branches.empty.no_branches',
            'website.branches.fields.name',
            'website.branches.fields.country',
            'website.branches.fields.city',
            'website.branches.fields.address',
            'website.branches.fields.phone',
            'website.branches.fields.email',
            'website.branches.fields.working_hours',
            'website.branches.fields.map',
            'website.branches.fields.available_courses',
            'website.branches.fields.groups',
            'website.groups.title',
            'website.groups.empty.no_groups',
            'website.groups.fields.name',
            'website.groups.fields.course',
            'website.groups.fields.branch',
            'website.groups.fields.start_date',
            'website.groups.fields.schedule',
            'website.groups.fields.available_places',
            'website.groups.fields.capacity',
            'website.groups.fields.status',
            'website.groups.actions.apply_to_group',
            'website.groups.statuses.recruiting',
            'website.groups.statuses.almost_full',
            'website.groups.statuses.full',
            'website.groups.statuses.closed',
            'website.groups.statuses.scheduled',
            'website.filters.kicker',
            'website.filters.title',
            'website.filters.subtitle',
            'website.filters.country',
            'website.filters.city',
            'website.filters.category',
            'website.filters.all_countries',
            'website.filters.all_cities',
            'website.filters.all_categories',
            'website.filters.apply',
            'website.filters.reset',
            'website.filters.active',
            'website.forms.apply.title',
            'website.forms.apply.subtitle',
            'website.forms.callback.title',
            'website.forms.callback.subtitle',
            'website.forms.contact.title',
            'website.forms.fields.full_name',
            'website.forms.fields.phone',
            'website.forms.fields.email',
            'website.forms.fields.course',
            'website.forms.fields.branch',
            'website.forms.fields.training_group',
            'website.forms.fields.preferred_time',
            'website.forms.fields.preferred_messenger',
            'website.forms.fields.comment',
            'website.forms.fields.consent',
            'website.forms.fields.callback_time',
            'website.forms.context.selected_course',
            'website.forms.context.selected_branch',
            'website.forms.messages.success',
            'website.forms.messages.callback_success',
            'website.forms.messages.contact_success',
            'website.forms.messages.error',
            'website.forms.messages.submitting',
            'website.forms.messages.thank_you_title',
            'website.forms.messages.thank_you_text',
            'website.faq.title',
            'website.faq.empty.no_faq',
            'website.faq.fields.question',
            'website.faq.fields.answer',
            'website.testimonials.title',
            'website.testimonials.empty.no_testimonials',
            'website.testimonials.fields.name',
            'website.testimonials.fields.text',
            'website.testimonials.fields.rating',
            'website.seo.fields.slug',
            'website.seo.fields.seo_title',
            'website.seo.fields.seo_description',
            'website.seo.fields.og_title',
            'website.seo.fields.og_description',
            'website.seo.fields.og_image',
            'website.seo.fields.canonical_url',
            'website.seo.fields.is_indexable',
            'website.admin.pages.title',
            'website.admin.pages.create_title',
            'website.admin.pages.edit_title',
            'website.admin.pages.types.home',
            'website.admin.pages.types.pricing',
            'website.admin.pages.types.contacts',
            'website.admin.pages.types.thank_you',
            'website.admin.pages.types.privacy_policy',
            'website.admin.pages.types.terms',
            'website.admin.pages.types.custom',
            'website.admin.courses.title',
            'website.admin.courses.create_title',
            'website.admin.courses.edit_title',
            'website.admin.branches.title',
            'website.admin.branches.create_title',
            'website.admin.branches.edit_title',
            'website.admin.leads.title',
            'website.admin.leads.empty.no_leads',
            'website.admin.settings.title',
            'website.admin.actions.create',
            'website.admin.actions.save',
            'website.admin.actions.publish',
            'website.admin.actions.unpublish',
            'website.admin.actions.hide',
            'website.admin.actions.delete',
            'website.admin.actions.archive',
            'website.admin.actions.preview',
            'website.admin.actions.open_public_page',
            'website.validation.phone_or_email_required',
            'website.validation.consent_required',
            'website.validation.invalid_public_course',
            'website.validation.invalid_public_branch',
            'website.validation.invalid_public_group',
            'website.validation.group_is_full',
            'website.validation.document_invalid',
            'website.validation.email_invalid',
            'website.validation.name_too_long',
            'website.validation.contact_too_long',
            'website.validation.text_too_long',
            'website.validation.format_invalid',
            'website.validation.language_invalid',
            'website.validation.budget_range',
            'website.validation.default_translation_required',
            'website.validation.invalid_slug',
            'website.validation.slug_already_exists',
            'website.validation.invalid_price',
            'website.validation.invalid_locale',
            'website.validation.page_cannot_be_published',
            'website.validation.course_cannot_be_published',
            'website.validation.branch_cannot_be_published',
            'website.validation.seo_title_too_long',
            'website.validation.seo_description_too_long',
            'permissions.website.view',
            'permissions.website.manage_pages',
            'permissions.website.manage_courses',
            'permissions.website.manage_course_categories',
            'permissions.website.manage_pricing',
            'permissions.website.manage_branches',
            'permissions.website.manage_groups',
            'permissions.website.manage_faq',
            'permissions.website.manage_testimonials',
            'permissions.website.manage_settings',
            'permissions.website.view_leads',
            'permissions.website.update_leads',
            'permissions.website.view_marketing',
            'permissions.website.preview',
            'validation.attributes.website_lead.full_name',
            'validation.attributes.website_lead.phone',
            'validation.attributes.website_lead.email',
            'validation.attributes.website_lead.course_id',
            'validation.attributes.website_lead.branch_id',
            'validation.attributes.website_lead.training_group_id',
            'validation.attributes.website_lead.consent_accepted',
            'validation.attributes.course.name_translations',
            'validation.attributes.course.slug',
            'validation.attributes.course.price',
            'validation.attributes.branch.name_translations',
            'validation.attributes.branch.address_translations',
            'validation.attributes.site_page.title_translations',
        ];
    }
}
