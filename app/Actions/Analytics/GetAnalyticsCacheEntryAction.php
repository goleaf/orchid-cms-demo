<?php

namespace App\Actions\Analytics;

use App\Models\AnalyticsCacheEntry;

class GetAnalyticsCacheEntryAction
{
    public function handle(string $key): ?AnalyticsCacheEntry
    {
        return AnalyticsCacheEntry::query()
            ->forKey($key)
            ->fresh()
            ->first();
    }
}
