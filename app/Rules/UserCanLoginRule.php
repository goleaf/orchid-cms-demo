<?php

namespace App\Rules;

use App\Actions\Security\CheckUserCanLoginAction;
use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UserCanLoginRule implements ValidationRule
{
    public function __construct(private readonly ?User $target = null) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $user = $this->target;

        if (! $user?->exists) {
            return;
        }

        if (! app(CheckUserCanLoginAction::class)->handle($user)['allowed']) {
            $fail(tkey('security.validation.user_cannot_login'));
        }
    }
}
