<?php

namespace App\Actions\Analytics;

use App\Models\AnalyticsCacheEntry;
use Carbon\CarbonInterface;

class PutAnalyticsCacheEntryAction
{
    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, string>  $tags
     */
    public function handle(
        string $key,
        array $data,
        array $tags = ['analytics'],
        ?CarbonInterface $expiresAt = null,
        ?CarbonInterface $calculatedAt = null,
    ): AnalyticsCacheEntry {
        $calculatedAt ??= now();

        return AnalyticsCacheEntry::query()->updateOrCreate(
            ['cache_key' => $key],
            [
                'data' => $data,
                'tags' => array_values($tags),
                'expires_at' => $expiresAt,
                'calculated_at' => $calculatedAt,
            ],
        );
    }
}
