<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Database\Factories\LearningTopicFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class LearningTopic extends Model
{
    /** @use HasFactory<LearningTopicFactory> */
    use HasFactory;

    use HasTranslations;
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'training_program_id',
        'course_module_id',
        'code',
        'title_translations',
        'description_translations',
        'topic_type',
        'duration_minutes',
        'sort_order',
        'is_required',
        'is_active',
        'created_by_id',
        'updated_by_id',
    ];

    protected $casts = [
        'title_translations' => 'array',
        'description_translations' => 'array',
        'duration_minutes' => 'integer',
        'sort_order' => 'integer',
        'is_required' => 'boolean',
        'is_active' => 'boolean',
        'deleted_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $topic): void {
            if (blank($topic->uuid)) {
                $topic->uuid = (string) Str::uuid();
            }
        });
    }

    public function learningProgram(): BelongsTo
    {
        return $this->belongsTo(LearningProgram::class, 'training_program_id');
    }

    public function trainingProgram(): BelongsTo
    {
        return $this->belongsTo(TrainingProgram::class);
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(LearningProgramModule::class, 'course_module_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_id');
    }

    public function displayTitle(?string $locale = null): string
    {
        return $this->getTranslation('title', $locale)
            ?: $this->code
            ?: (string) $this->getKey();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeByProgram(Builder $query, int|string|null $programId): Builder
    {
        return filled($programId) ? $query->where('training_program_id', $programId) : $query;
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }
}
