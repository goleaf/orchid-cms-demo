<?php

namespace App\Http\Requests\Security;

use App\Models\PermissionGroup;
use App\Rules\PermissionGroupCodeRule;
use App\Rules\TranslatedPermissionNameRequiredRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePermissionGroupRequest extends FormRequest
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
        $group = $this->targetGroup();

        return [
            'group.id' => ['nullable', 'integer', Rule::exists(PermissionGroup::class, 'id')],
            'group.code' => ['required', 'string', 'max:120', new PermissionGroupCodeRule, Rule::unique(PermissionGroup::class, 'code')->ignore($group?->getKey())],
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
     * @return array<string, mixed>
     */
    public function groupData(): array
    {
        $data = $this->validated('group');
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

    public function targetGroup(): ?PermissionGroup
    {
        $routeGroup = $this->route('permissionGroup');

        if ($routeGroup instanceof PermissionGroup) {
            return $routeGroup;
        }

        $id = $this->input('group.id');

        return filled($id) ? PermissionGroup::query()->find($id) : null;
    }
}
