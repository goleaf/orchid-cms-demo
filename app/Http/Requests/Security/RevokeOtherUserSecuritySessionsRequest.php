<?php

namespace App\Http\Requests\Security;

use App\Models\User;
use App\Rules\SessionIdHashRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RevokeOtherUserSecuritySessionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        if (! $user) {
            return false;
        }

        return $user->hasAccess('security.sessions.revoke')
            || ($user->hasAccess('security.sessions.revoke_own') && (int) $this->input('user_id') === (int) $user->getKey());
    }

    /**
     * @return array<string, array<int, mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', Rule::exists(User::class, 'id')],
            'current_session_id_hash' => ['nullable', 'string', new SessionIdHashRule],
        ];
    }
}
