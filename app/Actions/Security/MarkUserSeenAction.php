<?php

namespace App\Actions\Security;

use App\Models\User;
use Illuminate\Support\Facades\Schema;

class MarkUserSeenAction
{
    public function handle(User $user, int $throttleSeconds = 300): User
    {
        if (! Schema::hasColumn('users', 'last_seen_at')) {
            return $user;
        }

        if ($user->last_seen_at !== null && $user->last_seen_at->gt(now()->subSeconds($throttleSeconds))) {
            return $user;
        }

        $user->forceFill(['last_seen_at' => now()])->saveQuietly();

        return $user->refresh();
    }
}
