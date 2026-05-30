<?php

namespace App\Actions\Security;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class ClearForcePasswordChangeAction
{
    public function handle(User $user, ?User $actor = null, ?Request $request = null, bool $markPasswordChanged = false): User
    {
        $before = $user->only(['must_change_password', 'password_changed_at']);
        $data = [];

        if (Schema::hasColumn('users', 'must_change_password')) {
            $data['must_change_password'] = false;
        }

        if ($markPasswordChanged && Schema::hasColumn('users', 'password_changed_at')) {
            $data['password_changed_at'] = now();
        }

        if ($data !== []) {
            $user->forceFill($data)->save();
        }

        app(RecordAuditLogAction::class)->handle(
            'user_force_password_change_cleared',
            $actor,
            $user,
            $before,
            $user->only(['must_change_password', 'password_changed_at']),
            [],
            $request,
        );

        return $user->refresh();
    }
}
