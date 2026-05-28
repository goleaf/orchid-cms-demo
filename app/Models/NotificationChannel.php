<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Database\Factories\NotificationChannelFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NotificationChannel extends Model
{
    /** @use HasFactory<NotificationChannelFactory> */
    use HasFactory;

    use HasTranslations;

    public const CODE_INTERNAL = 'internal';
    public const CODE_EMAIL = 'email';
    public const CODE_PHONE = 'phone';
    public const CODE_SMS = 'sms';
    public const CODE_WHATSAPP = 'whatsapp';
    public const CODE_TELEGRAM = 'telegram';

    private const CODES = [
        self::CODE_INTERNAL,
        self::CODE_EMAIL,
        self::CODE_PHONE,
        self::CODE_SMS,
        self::CODE_WHATSAPP,
        self::CODE_TELEGRAM,
    ];

    protected $fillable = [
        'code',
        'name_translations',
        'description_translations',
        'driver',
        'provider',
        'is_system',
        'is_active',
        'supports_internal',
        'supports_external',
        'supports_templates',
        'supports_scheduling',
        'supports_delivery_status',
        'sort_order',
        'settings',
        'created_by_id',
        'updated_by_id',
    ];

    protected $casts = [
        'name_translations' => 'array',
        'description_translations' => 'array',
        'settings' => 'array',
        'is_system' => 'boolean',
        'is_active' => 'boolean',
        'supports_internal' => 'boolean',
        'supports_external' => 'boolean',
        'supports_templates' => 'boolean',
        'supports_scheduling' => 'boolean',
        'supports_delivery_status' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_id');
    }

    public function templates(): HasMany
    {
        return $this->hasMany(CommunicationTemplate::class);
    }

    public function reminders(): HasMany
    {
        return $this->hasMany(CommunicationReminder::class);
    }

    public function deliveryLogs(): HasMany
    {
        return $this->hasMany(NotificationDeliveryLog::class);
    }

    public function userPreferences(): HasMany
    {
        return $this->hasMany(UserNotificationPreference::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('code');
    }

    public function scopeForList(Builder $query): Builder
    {
        return $query->select([
            'id',
            'code',
            'name_translations',
            'description_translations',
            'driver',
            'provider',
            'is_system',
            'is_active',
            'supports_internal',
            'supports_external',
            'supports_templates',
            'supports_scheduling',
            'supports_delivery_status',
            'sort_order',
            'created_at',
            'updated_at',
        ]);
    }

    public function displayName(?string $locale = null): string
    {
        return $this->getTranslation('name', $locale)
            ?: tkey('communication.channels.'.$this->code)
            ?: str($this->code)->replace('_', ' ')->title()->toString();
    }

    public function isPlaceholder(): bool
    {
        return $this->driver === 'placeholder';
    }

    /**
     * @return array<int, string>
     */
    public static function codeValues(): array
    {
        return self::CODES;
    }

    /**
     * @return array<string, string>
     */
    public static function options(bool $activeOnly = true): array
    {
        $query = static::query()->ordered();

        if ($activeOnly) {
            $query->active();
        }

        return $query
            ->get(['id', 'code', 'name_translations'])
            ->mapWithKeys(fn (self $channel): array => [
                $channel->id => $channel->displayName(),
            ])
            ->all();
    }
}
