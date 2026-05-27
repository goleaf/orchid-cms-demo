<?php

namespace App\Http\Requests;

use App\Models\CourseCategory;
use App\Models\PricingPackage;
use App\Models\TrainingProgram;
use App\Rules\TranslatedFieldRequiredRule;
use App\Rules\ValidPriceRule;
use App\Rules\ValidSlugRule;
use App\Services\TranslatableContentManager;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PricingPackageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyAccess(['website.manage_courses', 'platform.lms.programs']) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $packageId = $this->input('package.id');
        $translationRules = app(TranslatableContentManager::class)->validationRules([
            'name',
            'description',
            'features',
        ]);
        $translationRules['name_translations'] = ['required', 'array', new TranslatedFieldRequiredRule];
        $translationRules['name_translations.*'] = ['nullable', 'string', 'max:255'];
        $translationRules['description_translations.*'] = ['nullable', 'string', 'max:5000'];
        $translationRules['features_translations.*'] = ['nullable', 'string', 'max:5000'];

        return [
            'package.id' => ['nullable', 'integer', Rule::exists(PricingPackage::class, 'id')],
            'package.course_id' => ['nullable', 'integer', Rule::exists(TrainingProgram::class, 'id')],
            'package.course_category_id' => ['nullable', 'integer', Rule::exists(CourseCategory::class, 'id')],
            'package.code' => [
                'nullable',
                'string',
                'max:80',
                Rule::unique('pricing_packages', 'code')->ignore($packageId),
            ],
            'package.slug' => [
                'required',
                'string',
                'max:255',
                new ValidSlugRule(PricingPackage::class, 'slug', $packageId),
            ],
            'package.price' => ['required', new ValidPriceRule(nullable: false)],
            'package.old_price' => ['nullable', new ValidPriceRule],
            'package.currency' => ['required', 'string', 'size:3'],
            'package.theory_hours' => ['nullable', 'numeric', 'min:0', 'max:1000'],
            'package.practice_hours' => ['nullable', 'numeric', 'min:0', 'max:1000'],
            'package.is_active' => ['nullable', 'boolean'],
            'package.is_visible_on_site' => ['nullable', 'boolean'],
            'package.is_featured' => ['nullable', 'boolean'],
            'package.sort_order' => ['nullable', 'integer', 'min:0', 'max:100000'],
            ...$translationRules,
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'package.slug.required' => tkey('website.validation.invalid_slug'),
            'package.price.required' => tkey('website.validation.invalid_price'),
            'package.currency.required' => tkey('website.validation.currency_required'),
            'package.currency.size' => tkey('website.validation.currency_required'),
            'name_translations.required' => tkey('website.validation.default_translation_required'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function packageData(): array
    {
        $validated = $this->validated();
        $package = $validated['package'];
        $translations = app(TranslatableContentManager::class)->extract($this, [
            'name',
            'description',
            'features',
        ]);
        $translations['features_translations'] = collect($translations['features_translations'] ?? [])
            ->map(fn (mixed $value): ?array => $this->lines(is_string($value) ? $value : null))
            ->all();

        return [
            'course_id' => $package['course_id'] ?? null,
            'course_category_id' => $package['course_category_id'] ?? null,
            'code' => $package['code'] ?? null,
            'slug' => $package['slug'],
            'price' => $package['price'],
            'old_price' => $package['old_price'] ?? null,
            'currency' => strtoupper((string) $package['currency']),
            'theory_hours' => $package['theory_hours'] ?? null,
            'practice_hours' => $package['practice_hours'] ?? null,
            'is_active' => (bool) ($package['is_active'] ?? false),
            'is_visible_on_site' => (bool) ($package['is_visible_on_site'] ?? false),
            'is_featured' => (bool) ($package['is_featured'] ?? false),
            'sort_order' => (int) ($package['sort_order'] ?? 0),
            ...$translations,
        ];
    }

    /**
     * @return array<int, string>|null
     */
    private function lines(?string $value): ?array
    {
        if (! filled($value)) {
            return null;
        }

        return str($value)
            ->replace(["\r\n", "\r"], "\n")
            ->explode("\n")
            ->map(fn (string $line): string => trim($line))
            ->filter()
            ->values()
            ->all();
    }
}
