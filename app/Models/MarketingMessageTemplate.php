<?php

namespace App\Models;

use Database\Factories\MarketingMessageTemplateFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarketingMessageTemplate extends Model
{
    /** @use HasFactory<MarketingMessageTemplateFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'channel',
        'subject',
        'body',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForChannel(Builder $query, ?string $channel): Builder
    {
        return $query->where(function (Builder $inner) use ($channel): void {
            $inner
                ->whereNull('channel')
                ->when($channel !== null, fn (Builder $builder) => $builder->orWhere('channel', $channel));
        });
    }

    public function displayName(): string
    {
        $channel = $this->channel === null
            ? 'Any'
            : str($this->channel)->replace('_', ' ')->title()->toString();

        return "{$this->name} ({$channel})";
    }
}
