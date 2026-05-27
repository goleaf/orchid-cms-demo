<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\HasWebsiteValidationAttributes;
use App\Models\CourseCategory;
use App\Rules\SeoMetadataRule;
use App\Rules\TranslatedFieldRequiredRule;
use App\Rules\ValidSlugRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;

class StoreCourseCategoryRequest extends FormRequest
{
    use HasWebsiteValidationAttributes;

    public function authorize(): bool
    {
        return $this->user()?->hasAnyAccess(['website.manage_course_categories', 'website.manage_courses', 'platform.lms.programs']) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'code' => ['nullable', 'string', 'max:80'],
            'slug' => ['required', 'string', 'max:255', new ValidSlugRule(CourseCategory::class, 'slug', $this->recordId())],
            'name_translations' => ['required', 'array', new TranslatedFieldRequiredRule],
            'name_translations.*' => ['nullable', 'string', 'max:255'],
            'description_translations' => ['nullable', 'array'],
            'description_translations.*' => ['nullable', 'string', 'max:10000'],
            'short_description_translations' => ['nullable', 'array'],
            'short_description_translations.*' => ['nullable', 'string', 'max:1000'],
            'seo_title_translations' => ['nullable', 'array', new SeoMetadataRule(70)],
            'seo_description_translations' => ['nullable', 'array', new SeoMetadataRule(180)],
            'image' => ['nullable', 'string', 'max:255'],
            'icon' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
            'is_visible_on_site' => ['nullable', 'boolean'],
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
        $routeModel = $this->route('courseCategory') ?? $this->route('category');

        return $routeModel instanceof Model
            ? $routeModel->getKey()
            : ($this->input('category.id') ?? $this->input('id'));
    }
}
