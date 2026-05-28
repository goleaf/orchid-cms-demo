<?php

namespace App\Http\Requests\Security;

use App\Models\User;
use App\Rules\BranchAccessRule;
use App\Rules\LastSuperadminRule;
use App\Rules\PasswordPolicyRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Orchid\Platform\Models\Role;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAccess('platform.systems.users') ?? false;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $user = $this->targetUser();

        return [
            'user' => [
                'required',
                'array',
                new LastSuperadminRule(
                    $user,
                    $this->has('user.roles') ? (array) $this->input('user.roles', []) : null,
                    $this->boolean('user.is_active', true),
                    filled($this->input('user.security_locked_at')),
                ),
            ],
            'user.name' => ['required', 'string', 'max:255'],
            'user.email' => [
                'required',
                'email',
                'max:255',
                Rule::unique(User::class, 'email')->ignore($user?->getKey()),
            ],
            'user.password' => [
                $user?->exists ? 'nullable' : 'required',
                'string',
                new PasswordPolicyRule,
            ],
            'user.roles' => ['nullable', 'array'],
            'user.roles.*' => ['integer', Rule::exists(Role::class, 'id')],
            'user.branch_ids' => ['nullable', new BranchAccessRule],
            'user.is_active' => ['nullable', 'boolean'],
            'user.security_locked_at' => ['nullable', 'date'],
            'user.security_lock_reason' => ['nullable', 'string', 'max:255'],
            'user.two_factor_placeholder_enabled' => ['nullable', 'boolean'],
            'permissions' => ['nullable', 'array'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'user.name.required' => tkey('security.validation.user_name_required'),
            'user.email.required' => tkey('security.validation.user_email_required'),
            'user.email.email' => tkey('security.validation.user_email_invalid'),
            'user.email.unique' => tkey('security.validation.user_email_unique'),
            'user.password.required' => tkey('security.validation.user_password_required'),
            'user.roles.*.exists' => tkey('security.validation.role_invalid'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'user.name' => tkey('validation.attributes.security.user_name'),
            'user.email' => tkey('validation.attributes.security.user_email'),
            'user.password' => tkey('validation.attributes.security.user_password'),
            'user.roles' => tkey('validation.attributes.security.user_roles'),
            'user.branch_ids' => tkey('validation.attributes.security.user_branches'),
            'user.is_active' => tkey('validation.attributes.security.user_active'),
            'user.security_lock_reason' => tkey('validation.attributes.security.lock_reason'),
        ];
    }

    private function targetUser(): ?User
    {
        $routeUser = $this->route('user');

        if ($routeUser instanceof User) {
            return $routeUser;
        }

        $id = $this->input('user.id');

        return filled($id) ? User::query()->find($id) : null;
    }
}
