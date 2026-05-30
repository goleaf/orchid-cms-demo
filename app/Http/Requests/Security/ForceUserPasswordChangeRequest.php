<?php

namespace App\Http\Requests\Security;

use App\Http\Requests\Security\Concerns\ResolvesTargetUser;
use App\Rules\UserCanForcePasswordChangeRule;
use Illuminate\Foundation\Http\FormRequest;

class ForceUserPasswordChangeRequest extends FormRequest
{
    use ResolvesTargetUser;

    public function authorize(): bool
    {
        return $this->user()?->hasAnyAccess(['security.users.force_password_change', 'platform.systems.users']) ?? false;
    }

    public function rules(): array
    {
        return [
            'user_id' => [new UserCanForcePasswordChangeRule($this->targetUser())],
            'revoke_sessions' => ['nullable', 'boolean'],
        ];
    }
}
