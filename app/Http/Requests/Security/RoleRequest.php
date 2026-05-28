<?php

namespace App\Http\Requests\Security;

use App\Rules\SuperadminRoleProtectedRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Orchid\Platform\Models\Role;

class RoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAccess('platform.systems.roles') ?? false;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $role = $this->targetRole();

        return [
            'role.name' => ['required', 'string', 'max:255'],
            'role.slug' => [
                'required',
                'string',
                'max:255',
                new SuperadminRoleProtectedRule($role),
                Rule::unique(Role::class, 'slug')->ignore($role?->getKey()),
            ],
            'permissions' => ['nullable', 'array'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'role.name.required' => tkey('security.validation.role_name_required'),
            'role.slug.required' => tkey('security.validation.role_slug_required'),
            'role.slug.unique' => tkey('security.validation.role_slug_unique'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'role.name' => tkey('validation.attributes.security.role_name'),
            'role.slug' => tkey('validation.attributes.security.role_slug'),
            'permissions' => tkey('validation.attributes.security.permissions'),
        ];
    }

    private function targetRole(): ?Role
    {
        $routeRole = $this->route('role');

        if ($routeRole instanceof Role) {
            return $routeRole;
        }

        $id = $this->input('role.id');

        return filled($id) ? Role::query()->find($id) : null;
    }
}
