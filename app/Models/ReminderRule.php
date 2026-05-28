<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Database\Factories\ReminderRuleFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReminderRule extends Model
{
    /** @use HasFactory<ReminderRuleFactory> */
    use HasFactory;

    use HasTranslations;

    public const TRIGGER_BEFORE_LESSON = 'before_lesson';

    public const TRIGGER_AFTER_SIGNUP = 'after_signup';

    public const TRIGGER_BEFORE_PAYMENT_DUE = 'before_payment_due';

    public const TRIGGER_BEFORE_EXAM = 'before_exam';

    public const TRIGGER_MANUAL = 'manual';

    protected $fillable = [
        'code',
        'name_translations',
        'trigger_type',
        'target_type',
        'template_id',
        'offset_minutes',
        'is_active',
        'metadata',
    ];

    protected $casts = [
        'name_translations' => 'array',
        'offset_minutes' => 'integer',
        'is_active' => 'boolean',
        'metadata' => 'array',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(NotificationTemplate::class, 'template_id');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(ReminderSchedule::class, 'rule_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForTarget(Builder $query, string $targetType): Builder
    {
        return $query->where('target_type', $targetType);
    }

    public function displayName(?string $locale = null): string
    {
        return $this->getTranslation('name', $locale)
            ?: str($this->code)->replace('_', ' ')->title()->toString();
    }

    /**
     * @return array<int, string>
     */
    public static function triggerValues(): array
    {
        return [
            self::TRIGGER_BEFORE_LESSON,
            self::TRIGGER_AFTER_SIGNUP,
            self::TRIGGER_BEFORE_PAYMENT_DUE,
            self::TRIGGER_BEFORE_EXAM,
            self::TRIGGER_MANUAL,
        ];
    }
}
