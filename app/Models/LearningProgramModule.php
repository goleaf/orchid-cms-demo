<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Database\Factories\LearningProgramModuleFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LearningProgramModule extends Model
{
    /** @use HasFactory<LearningProgramModuleFactory> */
    use HasFactory;

    use HasTranslations;

    protected $fillable = [
        'learning_program_id',
        'code',
        'type',
        'name_translations',
        'description_translations',
        'required_hours',
        'sort_order',
        'is_required',
        'is_active',
    ];

    protected $casts = [
        'name_translations' => 'array',
        'description_translations' => 'array',
        'required_hours' => 'decimal:2',
        'sort_order' => 'integer',
        'is_required' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function program(): BelongsTo
    {
        return $this->belongsTo(LearningProgram::class, 'learning_program_id');
    }

    public function learningProgram(): BelongsTo
    {
        return $this->program();
    }

    public function topics(): HasMany
    {
        return $this->hasMany(LearningTopic::class, 'learning_program_module_id');
    }

    public function displayName(?string $locale = null): string
    {
        return $this->getTranslation('name', $locale)
            ?: $this->code
            ?: (string) $this->getKey();
    }

    public function displayTitle(?string $locale = null): string
    {
        return $this->displayName($locale);
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->displayName();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForProgramOutline(Builder $query): Builder
    {
        return $query->select([
            'id',
            'learning_program_id',
            'code',
            'type',
            'name_translations',
            'description_translations',
            'required_hours',
            'sort_order',
            'is_required',
            'is_active',
        ])->orderBy('sort_order');
    }
}
