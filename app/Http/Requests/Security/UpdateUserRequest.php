<?php

namespace App\Http\Requests\Security;

use App\Http\Requests\Security\Concerns\ResolvesTargetUser;
use App\Rules\ActiveUserStatusRule;
use App\Rules\BranchAccessRule;
use App\Rules\PasswordPolicyRule;
use App\Rules\UniqueUserEmailRule;
use App\Rules\UserCanBeUpdatedRule;
use App\Rules\ValidUserLocaleRule;
use App\Rules\ValidUserStatusTransitionRule;
use App\Rules\ValidUserTimezoneRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Orchid\Platform\Models\Role;

class UpdateUserRequest extends FormRequest
{
    use ResolvesTargetUser;

    public function authorize(): bool
    {
        return $this->user()?->hasAnyAccess(['security.users.update', 'platform.systems.users']) ?? false;
    }

    /**
     * @return array<string, array<int, mixed>|string>
     */
    public function rules(): array
    {
        $target = $this->targetUser();

        return [
            'user' => ['nullable', 'array', new UserCanBeUpdatedRule($target, $this->user(), (array) $this->input('user', []))],
            'user.name' => ['sometimes', 'required', 'string', 'max:255'],
            'user.email' => ['sometimes', 'required', 'email', 'max:255', new UniqueUserEmailRule($target)],
            'user.password' => ['nullable', 'string', new PasswordPolicyRule],
            'user.status_id' => ['nullable', 'integer', new ActiveUserStatusRule, new ValidUserStatusTransitionRule($target, $this->user())],
            'user.preferred_locale' => ['nullable', 'string', 'max:12', new ValidUserLocaleRule],
            'user.timezone' => ['nullable', 'string', 'max:64', new ValidUserTimezoneRule],
            'user.must_change_password' => ['nullable', 'boolean'],
            'user.roles' => ['nullable', 'array'],
            'user.roles.*' => ['integer', Rule::exists(Role::class, 'id')],
            'user.branch_ids' => ['nullable', new BranchAccessRule],
        ];
    }
}
