<?php

namespace App\Actions\Security;

use App\Models\User;

class ResolveUserDisplayNameAction
{
    public function handle(User $user, ?string $locale = null): string
    {
        $user->loadMissing('staffProfile');

        $profileName = $user->staffProfile?->getTranslation('display_name', $locale);

        if (filled($profileName)) {
            return (string) $profileName;
        }

        if (filled($user->name)) {
            return (string) $user->name;
        }

        return (string) $user->email;
    }
}
