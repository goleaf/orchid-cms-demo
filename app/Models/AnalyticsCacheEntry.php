<?php

namespace App\Models;

use Database\Factories\AnalyticsCacheEntryFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnalyticsCacheEntry extends Model
{
    /** @use HasFactory<AnalyticsCacheEntryFactory> */
    use HasFactory;

    protected $fillable = [
        'cache_key',
        'data',
        'tags',
        'expires_at',
        'calculated_at',
    ];

    protected $casts = [
        'data' => 'array',
        'tags' => 'array',
        'expires_at' => 'datetime',
        'calculated_at' => 'datetime',
    ];

    public function isFresh(): bool
    {
        return $this->expires_at === null || $this->expires_at->isFuture();
    }

    public function isExpired(): bool
    {
        return ! $this->isFresh();
    }

    public function scopeFresh(Builder $query): Builder
    {
        return $query->where(function (Builder $query): void {
            $query->whereNull('expires_at')
                ->orWhere('expires_at', '>', now());
        });
    }

    public function scopeForKey(Builder $query, string $key): Builder
    {
        return $query->where('cache_key', $key);
    }

    public function scopeWithAnyTag(Builder $query, array $tags): Builder
    {
        $tags = array_values(array_filter($tags, fn (mixed $tag): bool => is_string($tag) && $tag !== ''));

        if ($tags === []) {
            return $query;
        }

        return $query->where(function (Builder $query) use ($tags): void {
            foreach ($tags as $index => $tag) {
                $method = $index === 0 ? 'whereJsonContains' : 'orWhereJsonContains';
                $query->{$method}('tags', $tag);
            }
        });
    }
}
