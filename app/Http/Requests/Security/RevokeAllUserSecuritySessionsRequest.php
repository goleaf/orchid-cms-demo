<?php

namespace App\Http\Requests\Security;

use App\Models\User;
use App\Rules\UserCanRevokeAllSessionsRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RevokeAllUserSecuritySessionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAccess('security.sessions.revoke_all') ?? false;
    }

    /**
     * @return array<string, array<int, mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', Rule::exists(User::class, 'id'), new UserCanRevokeAllSessionsRule($this->user())],
            'include_current' => ['nullable', 'boolean'],
        ];
    }
}
