<?php

namespace App\Actions\Security;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class DeleteUserAction
{
    public function handle(User $user, ?User $actor = null, ?Request $request = null): void
    {
        $user->loadMissing('roles');

        if ($user->isSuperadmin() && $this->activeSuperadminCountExcept($user) === 0) {
            throw ValidationException::withMessages([
                'user' => tkey('security.validation.last_superadmin'),
            ]);
        }

        app(RecordAuditLogAction::class)->handle(
            'user.deleted',
            $actor,
            $user,
            $user->only(['name', 'email', 'is_active', 'security_locked_at']),
            [],
            [],
            $request,
        );

        $user->delete();
    }

    private function activeSuperadminCountExcept(User $user): int
    {
        return User::query()
            ->activeForLogin()
            ->whereKeyNot($user->getKey())
            ->whereHas('roles', fn ($query) => $query->where('slug', 'superadmin'))
            ->count();
    }
}
