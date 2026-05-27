<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\HasWebsiteValidationAttributes;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\PricingPackage;
use App\Rules\TranslatedFieldRequiredRule;
use App\Rules\ValidPriceRule;
use App\Rules\ValidSlugRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePricingPackageRequest extends FormRequest
{
    use HasWebsiteValidationAttributes;

    public function authorize(): bool
    {
        return $this->user()?->hasAnyAccess(['website.manage_pricing', 'website.manage_courses', 'platform.lms.programs']) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'course_id' => ['nullable', 'integer', Rule::exists(Course::class, 'id')],
            'course_category_id' => ['nullable', 'integer', Rule::exists(CourseCategory::class, 'id')],
            'code' => ['nullable', 'string', 'max:80'],
            'slug' => ['nullable', 'string', 'max:255', new ValidSlugRule(PricingPackage::class, 'slug', $this->recordId())],
            'name_translations' => ['required', 'array', new TranslatedFieldRequiredRule],
            'name_translations.*' => ['nullable', 'string', 'max:255'],
            'description_translations' => ['nullable', 'array'],
            'description_translations.*' => ['nullable', 'string', 'max:5000'],
            'features_translations' => ['nullable', 'array'],
            'price' => ['nullable', new ValidPriceRule],
            'old_price' => ['nullable', new ValidPriceRule],
            'currency' => ['nullable', 'string', 'size:3'],
            'theory_hours' => ['nullable', 'numeric', 'min:0', 'max:1000'],
            'practice_hours' => ['nullable', 'numeric', 'min:0', 'max:1000'],
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
            'name_translations.required' => tkey('website.validation.default_translation_required'),
        ];
    }

    protected function recordId(): mixed
    {
        $routeModel = $this->route('pricingPackage') ?? $this->route('package');

        return $routeModel instanceof Model
            ? $routeModel->getKey()
            : ($this->input('package.id') ?? $this->input('id'));
    }
}
