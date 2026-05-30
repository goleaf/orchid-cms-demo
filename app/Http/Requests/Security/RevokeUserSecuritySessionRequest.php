<?php

namespace App\Http\Requests\Security;

use App\Models\UserSecuritySession;
use App\Rules\UserCanRevokeSessionRule;
use App\Rules\UserSecuritySessionCanBeRevokedRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RevokeUserSecuritySessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyAccess(['security.sessions.revoke', 'security.sessions.revoke_own']) ?? false;
    }

    /**
     * @return array<string, array<int, mixed>|string>
     */
    public function rules(): array
    {
        return [
            'session_id' => [
                'required',
                'integer',
                Rule::exists(UserSecuritySession::class, 'id'),
                new UserSecuritySessionCanBeRevokedRule,
                new UserCanRevokeSessionRule($this->user()),
            ],
        ];
    }

    public function sessionRecord(): UserSecuritySession
    {
        return UserSecuritySession::query()->findOrFail($this->validated('session_id'));
    }
}
