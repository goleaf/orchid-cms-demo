<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Database\Factories\NotificationTemplateVariableFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationTemplateVariable extends Model
{
    /** @use HasFactory<NotificationTemplateVariableFactory> */
    use HasFactory;

    use HasTranslations;

    protected $fillable = [
        'template_id',
        'key',
        'label_translations',
        'description_translations',
        'type',
        'is_required',
        'default_value',
        'sort_order',
        'metadata',
    ];

    protected $casts = [
        'label_translations' => 'array',
        'description_translations' => 'array',
        'is_required' => 'boolean',
        'sort_order' => 'integer',
        'metadata' => 'array',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(NotificationTemplate::class, 'template_id');
    }

    public function scopeRequired(Builder $query): Builder
    {
        return $query->where('is_required', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('key');
    }

    public function displayLabel(?string $locale = null): string
    {
        return $this->getTranslation('label', $locale)
            ?: str($this->key)->replace('_', ' ')->title()->toString();
    }
}
