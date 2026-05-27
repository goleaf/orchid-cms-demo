<?php

namespace App\Http\Requests\System;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LanguageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $permission = $this->routeIs('platform.system.languages.create')
            ? 'system.languages.create'
            : 'system.languages.update';

        return $this->user()?->hasAccess($permission) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $language = $this->route('language');
        $languageId = is_object($language) ? $language->getKey() : null;

        return [
            'language.code' => [
                'required',
                'string',
                'max:12',
                'alpha_dash',
                Rule::unique('languages', 'code')->ignore($languageId),
            ],
            'language.name' => ['required', 'string', 'max:255'],
            'language.native_name' => ['required', 'string', 'max:255'],
            'language.is_default' => ['nullable', 'boolean'],
            'language.is_active' => ['nullable', 'boolean'],
            'language.sort_order' => ['required', 'integer', 'min:0', 'max:999999'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function languageData(): array
    {
        $data = $this->validated('language');

        return [
            ...$data,
            'is_default' => (bool) ($data['is_default'] ?? false),
            'is_active' => (bool) ($data['is_active'] ?? false),
        ];
    }
}
