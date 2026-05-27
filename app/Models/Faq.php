<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Database\Factories\FaqFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Faq extends Model
{
    /** @use HasFactory<FaqFactory> */
    use HasFactory;

    use HasTranslations;
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'faqable_type',
        'faqable_id',
        'question_translations',
        'answer_translations',
        'is_active',
        'sort_order',
        'created_by_id',
        'updated_by_id',
    ];

    protected $casts = [
        'question_translations' => 'array',
        'answer_translations' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'deleted_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $faq): void {
            if (blank($faq->uuid)) {
                $faq->uuid = (string) Str::uuid();
            }
        });
    }

    public function faqable(): MorphTo
    {
        return $this->morphTo();
    }

    public function displayQuestion(?string $locale = null): string
    {
        return $this->getTranslation('question', $locale)
            ?: (string) $this->getKey();
    }

    public function displayAnswer(?string $locale = null): ?string
    {
        return $this->getTranslation('answer', $locale);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->displayQuestion();
    }

    public function getDisplayTitleAttribute(): string
    {
        return $this->displayQuestion();
    }
}
