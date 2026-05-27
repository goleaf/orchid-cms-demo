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
            'settings' => ['nullable', 'array'],
            'settings.*.key' => ['required_with:settings', 'string', 'max:120'],
            'settings.*.group' => ['nullable', 'string', 'max:120'],
            'settings.*.value' => ['nullable'],
            'settings.*.description' => ['nullable', 'string', 'max:2000'],
            'settings.*.is_public' => ['nullable', 'boolean'],
            'default_phone' => ['nullable', 'string', 'max:80'],
            'default_email' => ['nullable', 'email:rfc', 'max:190'],
            'default_currency' => ['nullable', 'string', 'size:3'],
            'social_links' => ['nullable', 'json'],
            'hero_image' => ['nullable', 'string', 'max:255'],
            'default_branch_id' => ['nullable', 'integer', 'exists:branches,id'],
            'cookie_notice_enabled' => ['nullable', 'boolean'],
            'analytics_enabled' => ['nullable', 'boolean'],
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

    /**
     * @return array<int, array<string, mixed>>
     */
    public function settingsData(): array
    {
        $validated = $this->validated();

        if (isset($validated['settings']) && is_array($validated['settings']) && $validated['settings'] !== []) {
            return $validated['settings'];
        }

        return [
            $this->settingPayload('default_phone', 'contacts', $validated['default_phone'] ?? null, true),
            $this->settingPayload('default_email', 'contacts', $validated['default_email'] ?? null, true),
            $this->settingPayload('default_currency', 'website', $validated['default_currency'] ?? 'EUR', true),
            $this->settingPayload('social_links', 'website', $this->jsonValue($validated['social_links'] ?? null), true),
            $this->settingPayload('hero_image', 'seo', $validated['hero_image'] ?? null, true),
            $this->settingPayload('default_branch_id', 'contacts', filled($validated['default_branch_id'] ?? null) ? (int) $validated['default_branch_id'] : null, false),
            $this->settingPayload('cookie_notice_enabled', 'analytics', (bool) ($validated['cookie_notice_enabled'] ?? false), true),
            $this->settingPayload('analytics_enabled', 'analytics', (bool) ($validated['analytics_enabled'] ?? false), false),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function settingPayload(string $key, string $group, mixed $value, bool $isPublic): array
    {
        return [
            'key' => $key,
            'group' => $group,
            'value' => $value,
            'description' => tkey('website.admin.settings.description'),
            'is_public' => $isPublic,
        ];
    }

    private function jsonValue(?string $value): mixed
    {
        if (! filled($value)) {
            return [];
        }

        return json_decode($value, true, 512, JSON_THROW_ON_ERROR);
    }
}
