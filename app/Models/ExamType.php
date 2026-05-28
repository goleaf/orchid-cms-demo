<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Database\Factories\ExamTypeFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamType extends Model
{
    /** @use HasFactory<ExamTypeFactory> */
    use HasFactory;

    use HasTranslations;

    protected $fillable = [
        'code',
        'name',
        'name_translations',
        'description_translations',
        'is_internal',
        'is_official',
        'is_theory',
        'is_practical',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'name_translations' => 'array',
        'description_translations' => 'array',
        'is_internal' => 'boolean',
        'is_official' => 'boolean',
        'is_theory' => 'boolean',
        'is_practical' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function admissionRules(): HasMany
    {
        return $this->hasMany(ExamAdmissionRule::class);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(ExamSession::class, 'type_id');
    }

    public function displayName(?string $locale = null): string
    {
        return $this->getTranslation('name', $locale)
            ?: $this->name
            ?: str($this->code)->replace(['_', '-'], ' ')->title()->toString();
    }

    public function displayDescription(?string $locale = null): ?string
    {
        return $this->getTranslation('description', $locale);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeInternal(Builder $query): Builder
    {
        return $query->where('is_internal', true);
    }

    public function scopeOfficial(Builder $query): Builder
    {
        return $query->where('is_official', true);
    }

    public function scopeTheory(Builder $query): Builder
    {
        return $query->where('is_theory', true);
    }

    public function scopePractical(Builder $query): Builder
    {
        return $query->where('is_practical', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('code');
    }

    public function scopeForDictionaryList(Builder $query): Builder
    {
        return $query->select([
            'id',
            'code',
            'name',
            'name_translations',
            'description_translations',
            'is_internal',
            'is_official',
            'is_theory',
            'is_practical',
            'is_active',
            'sort_order',
        ]);
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->displayName();
    }
}
