<?php

namespace App\Actions\Security;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ConfigureTwoFactorPlaceholderAction
{
    public function handle(User $user, bool $enabled, ?User $actor = null, ?Request $request = null): User
    {
        if ($enabled) {
            throw ValidationException::withMessages([
                'user.two_factor_placeholder_enabled' => tkey('security.validation.two_factor_not_available'),
            ]);
        }

        if ($user->exists) {
            $user->forceFill(['two_factor_placeholder_enabled' => false])->save();

            app(RecordAuditLogAction::class)->handle(
                'user.two_factor_placeholder_disabled',
                $actor,
                $user,
                [],
                ['two_factor_placeholder_enabled' => false],
                [],
                $request,
            );
        }

        return $user;
    }
}
