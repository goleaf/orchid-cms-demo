<?php

namespace App\Actions\Security;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ChangeUserPasswordAction
{
    public function handle(User $user, string $password, ?User $actor = null, ?Request $request = null): User
    {
        $user->forceFill([
            'password' => Hash::make($password),
            'password_changed_at' => now(),
        ])->save();

        app(RecordAuditLogAction::class)->handle(
            'user.password_changed',
            $actor,
            $user,
            [],
            ['password_changed_at' => $user->password_changed_at],
            [],
            $request,
        );

        app(RecordSecurityEventAction::class)->handle('password.changed', $user, 'info', [], $request);

        return $user;
    }
}
