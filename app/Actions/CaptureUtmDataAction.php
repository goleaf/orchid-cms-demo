<?php

namespace App\Actions;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CaptureUtmDataAction
{
    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    public function handle(Request $request, array $overrides = []): array
    {
        $sessionTracking = $request->hasSession()
            ? $request->session()->get(StoreUtmInSessionAction::SESSION_KEY, [])
            : [];
        $sessionTracking = is_array($sessionTracking) ? $sessionTracking : [];

        $payload = [
            'source' => $this->value($request, 'source', $sessionTracking['source'] ?? 'website', 120),
            'utm_source' => $this->value($request, 'utm_source', $sessionTracking['utm_source'] ?? null, 120),
            'utm_medium' => $this->value($request, 'utm_medium', $sessionTracking['utm_medium'] ?? null, 120),
            'utm_campaign' => $this->value($request, 'utm_campaign', $sessionTracking['utm_campaign'] ?? null, 120),
            'utm_content' => $this->value($request, 'utm_content', $sessionTracking['utm_content'] ?? null, 120),
            'utm_term' => $this->value($request, 'utm_term', $sessionTracking['utm_term'] ?? null, 120),
            'referrer_url' => $this->value($request, 'referrer_url', $sessionTracking['referrer_url'] ?? null, 255),
            'landing_page' => $this->value($request, 'landing_page', $sessionTracking['landing_page'] ?? $request->fullUrl(), 255),
            'form_page' => $this->value($request, 'form_page', $sessionTracking['form_page'] ?? $request->fullUrl(), 255),
            'form_name' => $this->value($request, 'form_name', 'enrollment', 120),
            'locale' => $this->value($request, 'locale', app()->getLocale(), 12),
            'ip_address' => $request->ip(),
            'user_agent' => $this->short((string) $request->userAgent(), 1000),
        ];

        $payload['referrer'] = $payload['referrer_url'];

        return array_merge($payload, $overrides);
    }

    private function value(Request $request, string $key, mixed $fallback, int $limit): ?string
    {
        $value = $request->input($key, $request->query($key, $fallback));

        return is_scalar($value) ? $this->short((string) $value, $limit) : null;
    }

    private function short(?string $value, int $limit): ?string
    {
        return filled($value) ? Str::limit($value, $limit, '') : null;
    }
}
