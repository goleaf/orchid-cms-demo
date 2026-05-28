<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Database\Factories\LearningProgramModuleFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class LearningProgramModule extends CourseModule
{
    use HasTranslations;

    protected $table = 'course_modules';

    protected $fillable = [
        'uuid',
        'training_program_id',
        'code',
        'title',
        'title_translations',
        'description_translations',
        'module_type',
        'sort_order',
        'duration_minutes',
        'is_required',
        'created_by_id',
        'updated_by_id',
    ];

    protected $casts = [
        'title_translations' => 'array',
        'description_translations' => 'array',
        'sort_order' => 'integer',
        'duration_minutes' => 'integer',
        'is_required' => 'boolean',
        'deleted_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $module): void {
            if (blank($module->uuid)) {
                $module->uuid = (string) Str::uuid();
            }
        });
    }

    protected static function newFactory(): Factory
    {
        return LearningProgramModuleFactory::new();
    }

    public function topics(): HasMany
    {
        return $this->hasMany(LearningTopic::class, 'course_module_id');
    }

    public function displayTitle(?string $locale = null): string
    {
        return $this->getTranslation('title', $locale)
            ?: $this->title
            ?: $this->code
            ?: (string) $this->getKey();
    }

    public function scopeForProgramOutline(Builder $query): Builder
    {
        return $query->select([
            'id',
            'uuid',
            'training_program_id',
            'code',
            'title',
            'title_translations',
            'description_translations',
            'module_type',
            'sort_order',
            'duration_minutes',
            'is_required',
        ])->orderBy('sort_order');
    }
}
