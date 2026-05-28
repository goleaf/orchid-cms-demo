<?php

namespace App\Http\Requests\Security;

use App\Models\PermissionGroup;
use App\Models\PermissionRegistryItem;
use App\Rules\CriticalPermissionRequiresSuperadminRule;
use App\Rules\PermissionRegistryCodeRule;
use App\Rules\TranslatedPermissionNameRequiredRule;
use App\Rules\ValidPermissionModuleRule;
use App\Rules\ValidPermissionRiskLevelRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePermissionRegistryItemRequest extends FormRequest
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
            'item.permission_group_id' => ['nullable', 'integer', Rule::exists(PermissionGroup::class, 'id')],
            'item.code' => ['required', 'string', 'max:180', new PermissionRegistryCodeRule, Rule::unique(PermissionRegistryItem::class, 'code')],
            'item.name_translations' => ['required', 'array', new TranslatedPermissionNameRequiredRule('item.name_translations')],
            'item.name_translations.*' => ['nullable', 'string', 'max:255'],
            'item.description_translations' => ['nullable', 'array'],
            'item.description_translations.*' => ['nullable', 'string', 'max:1000'],
            'item.module' => ['nullable', 'string', 'max:120', new ValidPermissionModuleRule],
            'item.risk_level' => ['required', 'string', new ValidPermissionRiskLevelRule, new CriticalPermissionRequiresSuperadminRule],
            'item.is_active' => ['nullable', 'boolean'],
            'item.is_system' => ['nullable', 'boolean'],
            'item.sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'item.code.required' => tkey('security.validation.permission_registry_code_invalid'),
            'item.code.unique' => tkey('security.validation.permission_registry_code_invalid'),
            'item.code.max' => tkey('security.validation.permission_registry_code_invalid'),
            'item.name_translations.required' => tkey('security.validation.default_permission_name_required'),
            'item.name_translations.array' => tkey('security.validation.default_permission_name_required'),
            'item.module.max' => tkey('security.validation.invalid_permission_module'),
            'item.risk_level.required' => tkey('security.validation.invalid_permission_risk_level'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'item.code' => tkey('security.permissions.fields.code'),
            'item.permission_group_id' => tkey('security.permissions.fields.group'),
            'item.module' => tkey('security.permissions.fields.module'),
            'item.risk_level' => tkey('security.permissions.fields.risk_level'),
            'item.name_translations' => tkey('security.permissions.fields.name'),
            'item.description_translations' => tkey('security.permissions.fields.description'),
            'item.is_active' => tkey('security.permissions.fields.is_active'),
            'item.is_system' => tkey('security.permissions.fields.is_system'),
            'item.sort_order' => tkey('security.permissions.fields.sort_order'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function itemData(): array
    {
        $data = $this->validated('item');
        $data['risk_level'] = $data['risk_level'] ?? PermissionRegistryItem::RISK_NORMAL;
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        $data['is_active'] = (bool) ($data['is_active'] ?? true);
        $data['is_system'] = (bool) ($data['is_system'] ?? true);

        return $data;
    }
}
