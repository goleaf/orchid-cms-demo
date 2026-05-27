<?php

namespace App\Http\Requests;

use App\Models\SitePage;
use App\Rules\PublishedPageRequirementRule;
use App\Rules\SeoMetadataRule;
use App\Rules\TranslatedFieldRequiredRule;
use App\Rules\ValidSlugRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSitePageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyAccess(['website.manage_pages', 'website.manage_settings']) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'page.id' => ['nullable', 'integer'],
            'type' => ['nullable', Rule::in(['home', 'pricing', 'contacts', 'thank_you', 'privacy_policy', 'terms', 'custom'])],
            'slug' => ['required', 'string', 'max:255', new ValidSlugRule(SitePage::class, 'slug', $this->recordId())],
            'title_translations' => ['required', 'array', new TranslatedFieldRequiredRule],
            'title_translations.*' => ['nullable', 'string', 'max:255'],
            'subtitle_translations' => ['nullable', 'array'],
            'content_translations' => ['nullable', 'array'],
            'excerpt_translations' => ['nullable', 'array'],
            'seo_title_translations' => ['nullable', 'array', new SeoMetadataRule(70)],
            'seo_description_translations' => ['nullable', 'array', new SeoMetadataRule(180)],
            'og_title_translations' => ['nullable', 'array', new SeoMetadataRule(90)],
            'og_description_translations' => ['nullable', 'array', new SeoMetadataRule(200)],
            'og_image' => ['nullable', 'string', 'max:255'],
            'template' => ['nullable', 'string', 'max:120'],
            'is_active' => ['nullable', 'boolean', new PublishedPageRequirementRule],
            'is_indexable' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'published_at' => ['nullable', 'date'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'slug.required' => tkey('website.validation.invalid_slug'),
            'title_translations.required' => tkey('website.validation.default_translation_required'),
        ];
    }

    protected function recordId(): mixed
    {
        $routeModel = $this->route('sitePage') ?? $this->route('page');

        return $routeModel instanceof Model
            ? $routeModel->getKey()
            : ($this->input('page.id') ?? $this->input('id'));
    }
}
