<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\HasWebsiteValidationAttributes;
use App\Models\LandingPage;
use App\Services\TranslatableContentManager;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LandingPageRequest extends FormRequest
{
    use HasWebsiteValidationAttributes;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->hasAnyAccess(['platform.content.home', 'website.manage_settings']) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'page.id' => ['required', 'integer', Rule::exists(LandingPage::class, 'id')],
            'page.slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('landing_pages', 'slug')->ignore($this->input('page.id')),
            ],
            'page.published_at' => ['nullable', 'date'],
            ...app(TranslatableContentManager::class)->validationRules([
                'title',
                'eyebrow',
                'hero_title',
                'hero_summary',
                'about_heading',
                'about_body',
                'offer_one_title',
                'offer_one_body',
                'offer_two_title',
                'offer_two_body',
                'offer_three_title',
                'offer_three_body',
            ]),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function pageData(): array
    {
        $validated = $this->validated();
        $translations = app(TranslatableContentManager::class)->extract($this, [
            'title',
            'eyebrow',
            'hero_title',
            'hero_summary',
            'about_heading',
            'about_body',
            'offer_one_title',
            'offer_one_body',
            'offer_two_title',
            'offer_two_body',
            'offer_three_title',
            'offer_three_body',
        ]);

        return [
            ...$validated['page'],
            ...$translations,
            'title' => $this->fallbackScalar($translations, 'title', tkey('website.nav.home')),
            'eyebrow' => $this->fallbackScalar($translations, 'eyebrow'),
            'hero_title' => $this->fallbackScalar($translations, 'hero_title', tkey('website.nav.home')),
            'hero_summary' => $this->fallbackScalar($translations, 'hero_summary', ''),
            'about_heading' => $this->fallbackScalar($translations, 'about_heading', ''),
            'about_body' => $this->fallbackScalar($translations, 'about_body', ''),
            'offer_one_title' => $this->fallbackScalar($translations, 'offer_one_title'),
            'offer_one_body' => $this->fallbackScalar($translations, 'offer_one_body'),
            'offer_two_title' => $this->fallbackScalar($translations, 'offer_two_title'),
            'offer_two_body' => $this->fallbackScalar($translations, 'offer_two_body'),
            'offer_three_title' => $this->fallbackScalar($translations, 'offer_three_title'),
            'offer_three_body' => $this->fallbackScalar($translations, 'offer_three_body'),
        ];
    }

    /**
     * @param  array<string, array<string, mixed>>  $translations
     */
    private function fallbackScalar(array $translations, string $field, ?string $fallback = null): ?string
    {
        $value = app(TranslatableContentManager::class)
            ->defaultValue($translations[$field.'_translations'] ?? []);

        return filled($value) ? (string) $value : $fallback;
    }
}
