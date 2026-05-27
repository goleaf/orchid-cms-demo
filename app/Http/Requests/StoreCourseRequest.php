<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\HasWebsiteValidationAttributes;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Rules\SeoMetadataRule;
use App\Rules\TranslatedFieldRequiredRule;
use App\Rules\ValidPriceRule;
use App\Rules\ValidSlugRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCourseRequest extends FormRequest
{
    use HasWebsiteValidationAttributes;

    public function authorize(): bool
    {
        return $this->user()?->hasAnyAccess(['website.manage_courses', 'platform.lms.programs']) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'course_category_id' => ['nullable', 'integer', Rule::exists(CourseCategory::class, 'id')],
            'code' => ['nullable', 'string', 'max:80'],
            'slug' => ['required', 'string', 'max:255', new ValidSlugRule(Course::class, 'slug', $this->recordId())],
            'name_translations' => ['required', 'array', new TranslatedFieldRequiredRule],
            'name_translations.*' => ['nullable', 'string', 'max:255'],
            'short_description_translations' => ['nullable', 'array'],
            'short_description_translations.*' => ['nullable', 'string', 'max:1000'],
            'description_translations' => ['nullable', 'array'],
            'description_translations.*' => ['nullable', 'string', 'max:10000'],
            'program_summary_translations' => ['nullable', 'array'],
            'program_summary_translations.*' => ['nullable', 'string', 'max:5000'],
            'includes_translations' => ['nullable', 'array'],
            'excludes_translations' => ['nullable', 'array'],
            'requirements_translations' => ['nullable', 'array'],
            'price' => ['nullable', new ValidPriceRule],
            'old_price' => ['nullable', new ValidPriceRule],
            'currency' => ['nullable', 'string', 'size:3'],
            'theory_hours' => ['nullable', 'numeric', 'min:0', 'max:1000'],
            'practice_hours' => ['nullable', 'numeric', 'min:0', 'max:1000'],
            'format' => ['nullable', Rule::in(['offline', 'online', 'hybrid', 'individual', 'group', 'mixed'])],
            'image' => ['nullable', 'string', 'max:255'],
            'icon' => ['nullable', 'string', 'max:255'],
            'seo_title_translations' => ['nullable', 'array', new SeoMetadataRule(70)],
            'seo_description_translations' => ['nullable', 'array', new SeoMetadataRule(180)],
            'og_title_translations' => ['nullable', 'array', new SeoMetadataRule(90)],
            'og_description_translations' => ['nullable', 'array', new SeoMetadataRule(200)],
            'og_image' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
            'is_visible_on_site' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
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
        $routeModel = $this->route('course') ?? $this->route('program');

        return $routeModel instanceof Model
            ? $routeModel->getKey()
            : ($this->input('course.id') ?? $this->input('program.id') ?? $this->input('id'));
    }
}
