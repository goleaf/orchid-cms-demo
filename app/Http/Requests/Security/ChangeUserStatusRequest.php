<?php

namespace App\Http\Requests\Security;

use App\Http\Requests\Security\Concerns\ResolvesTargetUser;
use App\Rules\ActiveUserStatusRule;
use App\Rules\CurrentUserLockoutRule;
use App\Rules\LastSuperadminUserProtectedRule;
use App\Rules\ValidUserStatusTransitionRule;
use App\Support\Security\UserLifecycle;
use Illuminate\Foundation\Http\FormRequest;

class ChangeUserStatusRequest extends FormRequest
{
    use ResolvesTargetUser;

    public function authorize(): bool
    {
        return $this->user()?->hasAnyAccess(['security.users.change_status', 'platform.systems.users']) ?? false;
    }

    public function rules(): array
    {
        $target = $this->targetUser();
        $status = app(UserLifecycle::class)->status($this->input('status_id'));
        $override = app(UserLifecycle::class)->actorCanOverrideStatus($this->user());

        return [
            'status_id' => [
                'required',
                'integer',
                new ActiveUserStatusRule,
                new ValidUserStatusTransitionRule($target, $this->user(), $override),
                new LastSuperadminUserProtectedRule($target, targetStatus: $status),
                new CurrentUserLockoutRule($target, $this->user(), targetStatus: $status, allowOverride: $override),
            ],
        ];
    }
}
