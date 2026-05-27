<?php

namespace App\Actions;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StoreUtmInSessionAction
{
    public const SESSION_KEY = 'site_tracking';

    /**
     * @var array<int, string>
     */
    private const FIRST_TOUCH_KEYS = [
        'source',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_content',
        'utm_term',
    ];

    /**
     * @return array<string, mixed>
     */
    public function handle(Request $request, bool $overwriteFirstTouch = false): array
    {
        if (! $request->hasSession() || ! $request->isMethod('GET')) {
            return [];
        }

        $tracking = $request->session()->get(self::SESSION_KEY, []);
        $tracking = is_array($tracking) ? $tracking : [];

        $tracking['current_page'] = $this->short($request->fullUrl(), 2048);
        $tracking['form_page'] = $this->short($request->fullUrl(), 255);

        if (! filled($tracking['landing_page'] ?? null)) {
            $tracking['landing_page'] = $this->short($request->fullUrl(), 2048);
        }

        $referrer = $request->headers->get('referer');

        if (filled($referrer) && ! filled($tracking['referrer_url'] ?? null)) {
            $tracking['referrer_url'] = $this->short($referrer, 255);
        }

        foreach (self::FIRST_TOUCH_KEYS as $key) {
            if (! $request->query->has($key) || ! filled($request->query($key))) {
                continue;
            }

            if ($overwriteFirstTouch || ! filled($tracking[$key] ?? null)) {
                $tracking[$key] = $this->short((string) $request->query($key), 255);
            }
        }

        $request->session()->put(self::SESSION_KEY, $tracking);

        return $tracking;
    }

    private function short(?string $value, int $limit): ?string
    {
        return filled($value) ? Str::limit($value, $limit, '') : null;
    }
}
