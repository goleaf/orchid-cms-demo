<?php

namespace App\Http\Requests\Security;

use App\Http\Requests\Security\Concerns\ResolvesTargetUser;
use Illuminate\Foundation\Http\FormRequest;

class ClearForcePasswordChangeRequest extends FormRequest
{
    use ResolvesTargetUser;

    public function authorize(): bool
    {
        return $this->user()?->hasAnyAccess(['security.users.force_password_change', 'platform.systems.users']) ?? false;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['nullable'],
            'mark_password_changed' => ['nullable', 'boolean'],
        ];
    }
}
