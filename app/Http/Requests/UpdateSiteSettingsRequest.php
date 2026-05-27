<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\HasWebsiteValidationAttributes;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSiteSettingsRequest extends FormRequest
{
    use HasWebsiteValidationAttributes;

    public function authorize(): bool
    {
        return $this->user()?->hasAnyAccess(['website.manage_settings']) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'settings' => ['required', 'array'],
            'settings.*.key' => ['required', 'string', 'max:120'],
            'settings.*.group' => ['nullable', 'string', 'max:120'],
            'settings.*.value' => ['nullable'],
            'settings.*.description' => ['nullable', 'string', 'max:2000'],
            'settings.*.is_public' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'settings.required' => tkey('website.validation.settings_required'),
            'settings.*.key.required' => tkey('website.validation.setting_key_required'),
        ];
    }
}
