<?php

namespace App\Actions\Security;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class UpdateUserProfileAction
{
    public function handle(User $user, array $attributes, ?Request $request = null): User
    {
        $before = $user->only(['name', 'email', 'preferred_locale']);

        $user->fill(Arr::only($attributes, ['name', 'email', 'preferred_locale']))->save();

        app(RecordAuditLogAction::class)->handle(
            'user.profile_updated',
            $user,
            $user,
            $before,
            $user->only(['name', 'email', 'preferred_locale']),
            [],
            $request,
        );

        return $user;
    }
}
