<?php

namespace App\Http\Requests\Security;

use App\Models\PermissionGroup;
use App\Rules\PermissionGroupCodeRule;
use App\Rules\TranslatedPermissionNameRequiredRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePermissionGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyAccess(['security.permissions.manage', 'platform.systems.roles']) ?? false;
    }

    /**
     * @return array<string, array<int, mixed>|string>
     */
    public function rules(): array
    {
        return [
            'group.code' => ['required', 'string', 'max:120', new PermissionGroupCodeRule, Rule::unique(PermissionGroup::class, 'code')],
            'group.name_translations' => ['required', 'array', new TranslatedPermissionNameRequiredRule('group.name_translations')],
            'group.name_translations.*' => ['nullable', 'string', 'max:255'],
            'group.description_translations' => ['nullable', 'array'],
            'group.description_translations.*' => ['nullable', 'string', 'max:1000'],
            'group.icon' => ['nullable', 'string', 'max:64'],
            'group.color' => ['nullable', 'string', 'max:32'],
            'group.sort_order' => ['nullable', 'integer', 'min:0'],
            'group.is_active' => ['nullable', 'boolean'],
            'group.is_system' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'group.code.required' => tkey('security.validation.permission_group_code_invalid'),
            'group.code.unique' => tkey('security.validation.permission_group_code_invalid'),
            'group.code.max' => tkey('security.validation.permission_group_code_invalid'),
            'group.name_translations.required' => tkey('security.validation.default_permission_name_required'),
            'group.name_translations.array' => tkey('security.validation.default_permission_name_required'),
            'group.name_translations.*.max' => tkey('security.validation.default_permission_name_required'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'group.code' => tkey('security.permissions.fields.code'),
            'group.name_translations' => tkey('security.permissions.fields.name'),
            'group.description_translations' => tkey('security.permissions.fields.description'),
            'group.sort_order' => tkey('security.permissions.fields.sort_order'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function groupData(): array
    {
        $data = $this->validated('group');
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        $data['is_active'] = (bool) ($data['is_active'] ?? true);
        $data['is_system'] = (bool) ($data['is_system'] ?? false);

        return $data;
    }
}
