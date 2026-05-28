<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Database\Factories\TrainingGroupSchedulePatternFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class TrainingGroupSchedulePattern extends Model
{
    /** @use HasFactory<TrainingGroupSchedulePatternFactory> */
    use HasFactory;

    use HasTranslations;
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'training_group_id',
        'title_translations',
        'day_of_week',
        'starts_at',
        'ends_at',
        'lesson_type',
        'classroom',
        'instructor_id',
        'is_active',
        'sort_order',
        'created_by_id',
        'updated_by_id',
    ];

    protected $casts = [
        'title_translations' => 'array',
        'day_of_week' => 'integer',
        'starts_at' => 'datetime:H:i',
        'ends_at' => 'datetime:H:i',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'deleted_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $pattern): void {
            if (blank($pattern->uuid)) {
                $pattern->uuid = (string) Str::uuid();
            }
        });
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(TrainingGroup::class, 'training_group_id');
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(Instructor::class);
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
            ?: tkey('education.schedule_patterns.fallback_title');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('day_of_week')->orderBy('starts_at');
    }
}
