<?php

namespace App\Support\Site;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SiteTracking
{
    public const SESSION_KEY = 'site_tracking';

    /**
     * @var array<int, string>
     */
    private const UTM_KEYS = [
        'source',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_term',
        'utm_content',
    ];

    public static function capture(Request $request): void
    {
        if (! $request->hasSession() || ! $request->isMethod('GET')) {
            return;
        }

        $tracking = $request->session()->get(self::SESSION_KEY, []);
        $tracking = is_array($tracking) ? $tracking : [];

        if (! filled($tracking['landing_page'] ?? null)) {
            $tracking['landing_page'] = self::short($request->fullUrl(), 2048);
        }

        $referrer = $request->headers->get('referer');

        if (filled($referrer) && ! filled($tracking['referrer_url'] ?? null)) {
            $tracking['referrer_url'] = self::short($referrer, 255);
        }

        foreach (self::UTM_KEYS as $key) {
            if ($request->query->has($key) && filled($request->query($key))) {
                $tracking[$key] = self::short((string) $request->query($key), 255);
            }
        }

        $request->session()->put(self::SESSION_KEY, $tracking);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    public static function payload(Request $request, array $overrides = []): array
    {
        $sessionTracking = $request->hasSession()
            ? $request->session()->get(self::SESSION_KEY, [])
            : [];
        $sessionTracking = is_array($sessionTracking) ? $sessionTracking : [];

        $payload = [
            'source' => self::shortValue($request, 'source', $sessionTracking['source'] ?? 'website', 120),
            'utm_source' => self::shortValue($request, 'utm_source', $sessionTracking['utm_source'] ?? null, 120),
            'utm_medium' => self::shortValue($request, 'utm_medium', $sessionTracking['utm_medium'] ?? null, 120),
            'utm_campaign' => self::shortValue($request, 'utm_campaign', $sessionTracking['utm_campaign'] ?? null, 120),
            'utm_term' => self::shortValue($request, 'utm_term', $sessionTracking['utm_term'] ?? null, 120),
            'utm_content' => self::shortValue($request, 'utm_content', $sessionTracking['utm_content'] ?? null, 120),
            'referrer_url' => self::shortValue($request, 'referrer_url', $sessionTracking['referrer_url'] ?? null, 255),
            'landing_page' => self::shortValue($request, 'landing_page', $sessionTracking['landing_page'] ?? $request->fullUrl(), 255),
            'form_page' => self::shortValue($request, 'form_page', $request->fullUrl(), 255),
            'form_name' => self::shortValue($request, 'form_name', 'enrollment', 120),
            'locale' => app()->getLocale(),
            'ip_address' => $request->ip(),
            'user_agent' => self::short((string) $request->userAgent(), 1000),
        ];

        return array_merge($payload, $overrides);
    }

    private static function shortValue(Request $request, string $key, mixed $fallback, int $limit): ?string
    {
        $value = $request->input($key, $request->query($key, $fallback));

        return is_scalar($value) ? self::short((string) $value, $limit) : null;
    }

    private static function short(?string $value, int $limit): ?string
    {
        if (! filled($value)) {
            return null;
        }

        return Str::limit($value, $limit, '');
    }
}
