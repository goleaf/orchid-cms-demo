<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Database\Factories\NotificationTemplateFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NotificationTemplate extends Model
{
    /** @use HasFactory<NotificationTemplateFactory> */
    use HasFactory;

    use HasTranslations;

    protected $fillable = [
        'code',
        'channel_id',
        'name_translations',
        'description_translations',
        'template_group',
        'is_active',
        'is_system',
    ];

    protected $casts = [
        'name_translations' => 'array',
        'description_translations' => 'array',
        'is_active' => 'boolean',
        'is_system' => 'boolean',
    ];

    public function channel(): BelongsTo
    {
        return $this->belongsTo(NotificationChannel::class);
    }

    public function versions(): HasMany
    {
        return $this->hasMany(NotificationTemplateVersion::class, 'template_id');
    }

    public function variables(): HasMany
    {
        return $this->hasMany(NotificationTemplateVariable::class, 'template_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(NotificationMessage::class, 'template_id');
    }

    public function reminderRules(): HasMany
    {
        return $this->hasMany(ReminderRule::class, 'template_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeSystem(Builder $query): Builder
    {
        return $query->where('is_system', true);
    }

    public function scopeForGroup(Builder $query, string $group): Builder
    {
        return $query->where('template_group', $group);
    }

    public function displayName(?string $locale = null): string
    {
        return $this->getTranslation('name', $locale)
            ?: str($this->code)->replace('_', ' ')->title()->toString();
    }
}
