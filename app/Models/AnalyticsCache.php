<?php

namespace App\Models;

use Database\Factories\AnalyticsCacheFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalyticsCache extends Model
{
    /** @use HasFactory<AnalyticsCacheFactory> */
    use HasFactory;

    protected $table = 'analytics_cache';

    protected $fillable = [
        'key',
        'group',
        'value',
        'tags',
        'expires_at',
        'refreshed_at',
        'created_by_id',
    ];

    protected $casts = [
        'value' => 'array',
        'tags' => 'array',
        'expires_at' => 'datetime',
        'refreshed_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function isFresh(): bool
    {
        return $this->expires_at === null || $this->expires_at->isFuture();
    }

    public function scopeFresh(Builder $query): Builder
    {
        return $query->where(function (Builder $query): void {
            $query->whereNull('expires_at')
                ->orWhere('expires_at', '>', now());
        });
    }
}
