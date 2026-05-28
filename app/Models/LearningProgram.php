<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Database\Factories\LearningProgramFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class LearningProgram extends Model
{
    /** @use HasFactory<LearningProgramFactory> */
    use HasFactory;

    use HasTranslations;
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'course_id',
        'course_category_id',
        'code',
        'name_translations',
        'description_translations',
        'is_default',
        'is_active',
        'sort_order',
        'created_by_id',
        'updated_by_id',
    ];

    protected $casts = [
        'name_translations' => 'array',
        'description_translations' => 'array',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'deleted_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $program): void {
            if (blank($program->uuid)) {
                $program->uuid = (string) Str::uuid();
            }
        });
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function courseCategory(): BelongsTo
    {
        return $this->belongsTo(CourseCategory::class, 'course_category_id');
    }

    public function modules(): HasMany
    {
        return $this->hasMany(LearningProgramModule::class);
    }

    public function learningModules(): HasMany
    {
        return $this->modules();
    }

    public function topics(): HasMany
    {
        return $this->hasManyThrough(LearningTopic::class, LearningProgramModule::class);
    }

    public function groups(): HasMany
    {
        return $this->hasMany(TrainingGroup::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_id');
    }

    public function displayName(?string $locale = null): string
    {
        return $this->getTranslation('name', $locale)
            ?: $this->code
            ?: (string) $this->getKey();
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->displayName();
    }

    public function getTotalRequiredHoursAttribute(): float
    {
        if ($this->relationLoaded('modules')) {
            return (float) $this->modules->sum(fn (LearningProgramModule $module): float => (float) $module->required_hours);
        }

        return (float) $this->modules()->sum('required_hours');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeDefault(Builder $query): Builder
    {
        return $query->where('is_default', true);
    }

    public function scopeByCourse(Builder $query, int|string|null $courseId): Builder
    {
        return filled($courseId) ? $query->where('course_id', $courseId) : $query;
    }

    public function scopeByCourseCategory(Builder $query, int|string|null $categoryId): Builder
    {
        return filled($categoryId) ? $query->where('course_category_id', $categoryId) : $query;
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if (blank($search)) {
            return $query;
        }

        $search = (string) $search;

        return $query->where(function (Builder $query) use ($search): void {
            $query
                ->where('uuid', 'like', '%'.$search.'%')
                ->orWhere('code', 'like', '%'.$search.'%')
                ->orWhere('name_translations', 'like', '%'.$search.'%');

            if (is_numeric($search)) {
                $query->orWhere('id', (int) $search);
            }
        });
    }
}
