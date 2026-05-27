<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\HasWebsiteValidationAttributes;
use App\Models\Branch;
use App\Rules\PublicPageIndexableRule;
use App\Rules\SeoDescriptionLengthRule;
use App\Rules\SeoTitleLengthRule;
use App\Rules\TranslatedFieldRequiredRule;
use App\Rules\ValidCanonicalUrlRule;
use App\Rules\ValidSlugRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;

class StoreBranchRequest extends FormRequest
{
    use HasWebsiteValidationAttributes;

    public function authorize(): bool
    {
        return $this->user()?->hasAnyAccess(['website.manage_branches', 'platform.operations.branches']) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'code' => ['nullable', 'string', 'max:80'],
            'slug' => ['required', 'string', 'max:255', new ValidSlugRule(Branch::class, 'slug', $this->recordId())],
            'name_translations' => ['required', 'array', new TranslatedFieldRequiredRule],
            'name_translations.*' => ['nullable', 'string', 'max:255'],
            'city_translations' => ['nullable', 'array'],
            'address_translations' => ['nullable', 'array'],
            'description_translations' => ['nullable', 'array'],
            'working_hours_translations' => ['nullable', 'array'],
            'phone' => ['nullable', 'string', 'max:80'],
            'email' => ['nullable', 'email:rfc', 'max:190'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'map_url' => ['nullable', 'string', 'max:2000'],
            'image' => ['nullable', 'string', 'max:255'],
            'seo_title_translations' => ['nullable', 'array', new SeoTitleLengthRule],
            'seo_description_translations' => ['nullable', 'array', new SeoDescriptionLengthRule],
            'og_title_translations' => ['nullable', 'array'],
            'og_description_translations' => ['nullable', 'array'],
            'canonical_url' => ['nullable', 'string', 'max:255', new ValidCanonicalUrlRule],
            'open_graph_image' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
            'is_visible_on_site' => ['nullable', 'boolean'],
            'is_indexable' => ['nullable', 'boolean', new PublicPageIndexableRule],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:100000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'slug.required' => tkey('website.validation.invalid_slug'),
            'name_translations.required' => tkey('website.validation.default_translation_required'),
        ];
    }

    protected function recordId(): mixed
    {
        $routeModel = $this->route('branch');

        return $routeModel instanceof Model
            ? $routeModel->getKey()
            : ($this->input('branch.id') ?? $this->input('id'));
    }
}
