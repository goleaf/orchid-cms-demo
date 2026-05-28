<?php

namespace App\Actions\Analytics;

use App\Models\AnalyticsCacheEntry;

class ClearAnalyticsCacheAction
{
    /**
     * @param  array<int, string>|null  $tags
     */
    public function handle(?array $tags = null, ?string $key = null): int
    {
        $query = AnalyticsCacheEntry::query();

        if ($key !== null) {
            $query->forKey($key);
        }

        if ($tags !== null) {
            $tags = array_values(array_filter($tags, fn (mixed $tag): bool => is_string($tag) && $tag !== ''));

            if ($tags === []) {
                return 0;
            }

            $query->withAnyTag($tags);
        }

        return $query->delete();
    }
}
