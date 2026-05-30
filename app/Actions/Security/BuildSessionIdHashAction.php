<?php

namespace App\Actions\Security;

class BuildSessionIdHashAction
{
    public function handle(?string $sessionId): ?string
    {
        if (blank($sessionId)) {
            return null;
        }

        return hash_hmac('sha256', $sessionId, (string) (config('app.key') ?: 'local-security-key'));
    }
}
