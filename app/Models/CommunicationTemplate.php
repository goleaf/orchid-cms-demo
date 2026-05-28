<?php

namespace App\Models;

use App\Enums\CommunicationTemplateType;
use App\Models\Concerns\HasTranslations;
use Database\Factories\CommunicationTemplateFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CommunicationTemplate extends Model
{
    /** @use HasFactory<CommunicationTemplateFactory> */
    use HasFactory;

    use HasTranslations;

    public const TYPE_GENERAL = 'general';

    public const TYPE_INTERNAL = 'internal';

    public const TYPE_STUDENT = 'student';

    public const TYPE_LEAD = 'lead';

    public const TYPE_REMINDER = 'reminder';

    protected $fillable = [
        'code',
        'type',
        'notification_channel_id',
        'channel',
        'name_translations',
        'subject_translations',
        'body_translations',
        'variable_keys',
        'is_system',
        'is_active',
        'sort_order',
        'metadata',
        'created_by_id',
        'updated_by_id',
    ];

    protected $casts = [
        'name_translations' => 'array',
        'subject_translations' => 'array',
        'body_translations' => 'array',
        'variable_keys' => 'array',
        'metadata' => 'array',
        'is_system' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function notificationChannel(): BelongsTo
    {
        return $this->belongsTo(NotificationChannel::class);
    }

    public function channelRecord(): BelongsTo
    {
        return $this->notificationChannel();
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_id');
    }

    public function reminders(): HasMany
    {
        return $this->hasMany(CommunicationReminder::class);
    }

    public function deliveryLogs(): HasMany
    {
        return $this->hasMany(NotificationDeliveryLog::class);
    }

    public function studentCommunications(): HasMany
    {
        return $this->hasMany(StudentCommunication::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForChannel(Builder $query, NotificationChannel|int|string|null $channel): Builder
    {
        $channelId = $channel instanceof NotificationChannel ? $channel->id : $channel;

        return $query->where(function (Builder $inner) use ($channelId): void {
            $inner->whereNull('notification_channel_id');

            if (filled($channelId)) {
                $inner->orWhere('notification_channel_id', $channelId);
            }
        });
    }

    public function scopeForType(Builder $query, ?string $type): Builder
    {
        return filled($type) ? $query->where('type', $type) : $query;
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public function scopeForList(Builder $query): Builder
    {
        return $query->select([
            'id',
            'code',
            'type',
            'notification_channel_id',
            'channel',
            'name_translations',
            'subject_translations',
            'body_translations',
            'variable_keys',
            'is_system',
            'is_active',
            'sort_order',
            'created_at',
            'updated_at',
        ]);
    }

    public function displayName(?string $locale = null): string
    {
        return $this->getTranslation('name', $locale)
            ?: ($this->code ?: tkey('communication.templates.fallback_name'));
    }

    public function subject(?string $locale = null): ?string
    {
        return $this->getTranslation('subject', $locale);
    }

    public function body(?string $locale = null): ?string
    {
        return $this->getTranslation('body', $locale);
    }

    public function typeLabel(): string
    {
        return tkey('communication.templates.types.'.$this->type);
    }

    public function channelLabel(): string
    {
        return $this->notificationChannel?->displayName()
            ?: tkey('communication.channels.any');
    }

    /**
     * @return array<int, string>
     */
    public static function typeValues(): array
    {
        return CommunicationTemplateType::values();
    }

    /**
     * @return array<string, string>
     */
    public static function typeOptions(): array
    {
        return collect(self::typeValues())
            ->mapWithKeys(fn (string $type): array => [$type => tkey('communication.templates.types.'.$type)])
            ->all();
    }
}
