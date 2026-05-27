<?php

namespace App\Http\Requests\System;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TranslationStringRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->hasAccess('system.translations.update') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $translationString = $this->route('translationString');
        $translationStringId = is_object($translationString) ? $translationString->getKey() : null;

        return [
            'translation.group' => ['nullable', 'string', 'max:255'],
            'translation.key' => [
                'required',
                'string',
                'max:255',
                Rule::unique('translation_strings', 'key')->ignore($translationStringId),
            ],
            'translation.description' => ['nullable', 'string', 'max:2000'],
            'translation.is_system' => ['nullable', 'boolean'],
            'values' => ['nullable', 'array'],
            'values.*.value' => ['nullable', 'string'],
            'values.*.is_approved' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function translationData(): array
    {
        $data = $this->validated('translation');

        return [
            ...$data,
            'group' => $data['group'] ?? null,
            'description' => $data['description'] ?? null,
            'is_system' => (bool) ($data['is_system'] ?? false),
        ];
    }

    /**
     * @return array<string, array{value?: string|null, is_approved?: bool}>
     */
    public function valueData(): array
    {
        return collect($this->validated('values') ?? [])
            ->map(fn (array $value): array => [
                'value' => $value['value'] ?? null,
                'is_approved' => (bool) ($value['is_approved'] ?? false),
            ])
            ->all();
    }
}
