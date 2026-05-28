<?php

namespace App\Actions\Security\Concerns;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

trait HashesSecurityContext
{
    protected function hashIdentifier(?string $value): string
    {
        return hash_hmac('sha256', Str::lower(trim((string) $value)), $this->hashKey());
    }

    protected function hashSessionId(?string $sessionId): string
    {
        return hash_hmac('sha256', (string) $sessionId, $this->hashKey());
    }

    protected function userAgentHash(?Request $request = null): ?string
    {
        $userAgent = $this->requestInstance($request)->userAgent();

        return filled($userAgent) ? hash('sha256', $userAgent) : null;
    }

    protected function userAgentPreview(?Request $request = null): ?string
    {
        $userAgent = $this->requestInstance($request)->userAgent();

        return filled($userAgent) ? Str::limit($userAgent, 160, '') : null;
    }

    protected function requestInstance(?Request $request = null): Request
    {
        return $request ?? request();
    }

    private function hashKey(): string
    {
        return (string) (config('app.key') ?: 'local-security-key');
    }
}
