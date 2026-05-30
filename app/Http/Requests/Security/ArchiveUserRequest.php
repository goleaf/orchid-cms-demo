<?php

namespace App\Http\Requests\Security;

use App\Http\Requests\Security\Concerns\ResolvesTargetUser;
use App\Rules\UserCanBeArchivedRule;
use Illuminate\Foundation\Http\FormRequest;

class ArchiveUserRequest extends FormRequest
{
    use ResolvesTargetUser;

    public function authorize(): bool
    {
        return $this->user()?->hasAnyAccess(['security.users.archive', 'platform.systems.users']) ?? false;
    }

    public function rules(): array
    {
        return [
            'user_id' => [new UserCanBeArchivedRule($this->targetUser(), $this->user(), $this->boolean('override_self'))],
            'override_self' => ['nullable', 'boolean'],
        ];
    }
}
