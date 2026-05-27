<?php

namespace App\Http\Requests;

use App\Rules\TranslatedFieldRequiredRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreFaqRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyAccess(['website.manage_pages', 'website.manage_courses', 'website.manage_branches']) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'faqable_type' => ['nullable', 'string', 'max:255'],
            'faqable_id' => ['nullable', 'integer'],
            'question_translations' => ['required', 'array', new TranslatedFieldRequiredRule],
            'question_translations.*' => ['nullable', 'string', 'max:500'],
            'answer_translations' => ['required', 'array', new TranslatedFieldRequiredRule],
            'answer_translations.*' => ['nullable', 'string', 'max:5000'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:100000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'question_translations.required' => tkey('website.validation.default_translation_required'),
            'answer_translations.required' => tkey('website.validation.default_translation_required'),
        ];
    }
}
