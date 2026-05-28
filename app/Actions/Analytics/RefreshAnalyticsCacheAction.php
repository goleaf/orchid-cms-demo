<?php

namespace App\Actions\Analytics;

use App\Models\AnalyticsCache;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Schema;

class RefreshAnalyticsCacheAction
{
    /**
     * @param  array<string, mixed>  $value
     * @param  array<int, string>  $tags
     */
    public function handle(
        string $key,
        array $value,
        string $group = 'analytics',
        int $ttlMinutes = 15,
        array $tags = ['analytics'],
        ?User $user = null,
        ?CarbonInterface $refreshedAt = null,
    ): AnalyticsCache {
        $refreshedAt ??= now();
        $expiresAt = $ttlMinutes > 0 ? $refreshedAt->copy()->addMinutes($ttlMinutes) : null;

        if (Schema::hasTable('analytics_cache_entries')) {
            app(PutAnalyticsCacheEntryAction::class)->handle(
                $key,
                $value,
                array_values(array_unique(array_filter([...$tags, $group]))),
                $expiresAt,
                $refreshedAt,
            );
        }

        return AnalyticsCache::query()->updateOrCreate(
            ['key' => $key],
            [
                'group' => $group,
                'value' => $value,
                'tags' => $tags,
                'expires_at' => $expiresAt,
                'refreshed_at' => $refreshedAt,
                'created_by_id' => $user?->id,
            ],
        );
    }
}
