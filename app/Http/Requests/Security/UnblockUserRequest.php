<?php

namespace App\Http\Requests\Security;

use App\Http\Requests\Security\Concerns\ResolvesTargetUser;
use App\Rules\UserCanBeUnblockedRule;
use Illuminate\Foundation\Http\FormRequest;

class UnblockUserRequest extends FormRequest
{
    use ResolvesTargetUser;

    public function authorize(): bool
    {
        return $this->user()?->hasAnyAccess(['security.users.unblock', 'platform.systems.users']) ?? false;
    }

    public function rules(): array
    {
        return [
            'user_id' => [new UserCanBeUnblockedRule($this->targetUser())],
        ];
    }
}
