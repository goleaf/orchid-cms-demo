<?php

namespace App\Http\Requests\Security;

use App\Rules\ActiveUserStatusRule;
use App\Rules\BranchAccessRule;
use App\Rules\PasswordPolicyRule;
use App\Rules\StaffPhoneRule;
use App\Rules\StaffWorkEmailRule;
use App\Rules\UniqueUserEmailRule;
use App\Rules\ValidUserLocaleRule;
use App\Rules\ValidUserTimezoneRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Orchid\Platform\Models\Role;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyAccess(['security.users.create', 'platform.systems.users']) ?? false;
    }

    /**
     * @return array<string, array<int, mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user.name' => ['required', 'string', 'max:255'],
            'user.email' => ['required', 'email', 'max:255', new UniqueUserEmailRule],
            'user.password' => ['nullable', 'string', new PasswordPolicyRule],
            'user.status_id' => ['nullable', 'integer', new ActiveUserStatusRule],
            'user.preferred_locale' => ['nullable', 'string', 'max:12', new ValidUserLocaleRule],
            'user.timezone' => ['nullable', 'string', 'max:64', new ValidUserTimezoneRule],
            'user.must_change_password' => ['nullable', 'boolean'],
            'user.roles' => ['nullable', 'array'],
            'user.roles.*' => ['integer', Rule::exists(Role::class, 'id')],
            'user.branch_ids' => ['nullable', new BranchAccessRule],
            'user.staff_profile' => ['nullable', 'array'],
            'user.staff_profile.work_email' => ['nullable', 'string', 'max:255', new StaffWorkEmailRule],
            'user.staff_profile.phone' => ['nullable', 'string', 'max:64', new StaffPhoneRule],
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
        ];
    }
}
