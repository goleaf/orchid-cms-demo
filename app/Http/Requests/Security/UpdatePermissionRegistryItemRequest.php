<?php

namespace App\Http\Requests\Security;

use App\Models\PermissionGroup;
use App\Models\PermissionRegistryItem;
use App\Rules\CriticalPermissionRequiresSuperadminRule;
use App\Rules\PermissionRegistryCodeRule;
use App\Rules\PermissionRegistryItemCanBeChangedRule;
use App\Rules\SystemPermissionCodeProtectedRule;
use App\Rules\TranslatedPermissionNameRequiredRule;
use App\Rules\ValidPermissionModuleRule;
use App\Rules\ValidPermissionRiskLevelRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePermissionRegistryItemRequest extends FormRequest
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
        $item = $this->targetItem();

        return [
            'item' => ['required', 'array', new PermissionRegistryItemCanBeChangedRule($item)],
            'item.id' => ['nullable', 'integer', Rule::exists(PermissionRegistryItem::class, 'id')],
            'item.permission_group_id' => ['nullable', 'integer', Rule::exists(PermissionGroup::class, 'id')],
            'item.code' => [
                'required',
                'string',
                'max:180',
                new PermissionRegistryCodeRule,
                new SystemPermissionCodeProtectedRule($item),
                Rule::unique(PermissionRegistryItem::class, 'code')->ignore($item?->getKey()),
            ],
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
     * @return array<string, mixed>
     */
    public function itemData(): array
    {
        $data = $this->validated('item');
        unset($data['id']);

        if (array_key_exists('sort_order', $data)) {
            $data['sort_order'] = (int) $data['sort_order'];
        }

        foreach (['is_active', 'is_system'] as $field) {
            if (array_key_exists($field, $data)) {
                $data[$field] = (bool) $data[$field];
            }
        }

        return $data;
    }

    public function targetItem(): ?PermissionRegistryItem
    {
        $routeItem = $this->route('permissionRegistryItem');

        if ($routeItem instanceof PermissionRegistryItem) {
            return $routeItem;
        }

        $id = $this->input('item.id');

        return filled($id) ? PermissionRegistryItem::query()->find($id) : null;
    }
}
