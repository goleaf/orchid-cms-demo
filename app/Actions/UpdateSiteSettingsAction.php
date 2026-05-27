<?php

namespace App\Actions;

use App\Models\SiteSetting;
use Illuminate\Support\Collection;

class UpdateSiteSettingsAction
{
    /**
     * @param  array<string, mixed>  $settings
     * @return Collection<int, SiteSetting>
     */
    public function handle(array $settings): Collection
    {
        if (array_key_exists('key', $settings)) {
            $settings = [$settings];
        }

        return collect($settings)
            ->map(function (mixed $value, mixed $key): SiteSetting {
                $payload = is_array($value)
                    ? $value
                    : ['key' => (string) $key, 'value' => $value];

                return SiteSetting::query()->updateOrCreate(
                    ['key' => (string) $payload['key']],
                    [
                        'group' => $payload['group'] ?? 'website',
                        'value' => $payload['value'] ?? null,
                        'description' => $payload['description'] ?? null,
                        'is_public' => (bool) ($payload['is_public'] ?? false),
                    ],
                );
            })
            ->values();
    }
}
