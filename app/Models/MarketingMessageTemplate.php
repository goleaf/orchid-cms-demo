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

    private const CHANNELS = [
        'phone',
        'sms',
        'email',
        'whatsapp',
        'telegram',
        'viber',
        'web_form',
    ];

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

    public function scopeForTemplateList(Builder $query): Builder
    {
        return $query->select([
            'id',
            'name',
            'channel',
            'subject',
            'body',
            'is_active',
            'sort_order',
            'created_at',
            'updated_at',
        ]);
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
        return "{$this->name} ({$this->channelLabel()})";
    }

    public function channelLabel(): string
    {
        if ($this->channel === null) {
            return tkey('crm.communication.channels.any');
        }

        return tkey('crm.communication.channels.'.$this->channel);
    }

    /**
     * @return array<int, string>
     */
    public static function channelValues(): array
    {
        return self::CHANNELS;
    }

    /**
     * @return array<string, string>
     */
    public static function channelOptions(): array
    {
        return collect(self::CHANNELS)
            ->mapWithKeys(fn (string $channel): array => [
                $channel => tkey('crm.communication.channels.'.$channel),
            ])
            ->all();
    }
}
