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
        'type',
        'day_of_week',
        'start_time',
        'end_time',
        'starts_at',
        'ends_at',
        'lesson_type',
        'classroom',
        'classroom_id',
        'location_translations',
        'notes_translations',
        'instructor_id',
        'is_active',
        'sort_order',
        'created_by_id',
        'updated_by_id',
    ];

    protected $casts = [
        'title_translations' => 'array',
        'location_translations' => 'array',
        'notes_translations' => 'array',
        'day_of_week' => 'integer',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
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

            $pattern->syncAliases();
        });

        static::saving(function (self $pattern): void {
            $pattern->syncAliases();
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

    public function getDisplayDayAttribute(): string
    {
        $days = [
            1 => 'monday',
            2 => 'tuesday',
            3 => 'wednesday',
            4 => 'thursday',
            5 => 'friday',
            6 => 'saturday',
            7 => 'sunday',
        ];

        $key = $days[$this->day_of_week] ?? null;

        return $key === null ? '-' : tkey('common.days.'.$key);
    }

    public function getDisplayTimeRangeAttribute(): string
    {
        $start = $this->start_time?->format('H:i') ?? $this->starts_at?->format('H:i');
        $end = $this->end_time?->format('H:i') ?? $this->ends_at?->format('H:i');

        return filled($start) && filled($end) ? $start.'-'.$end : '-';
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeByDayOfWeek(Builder $query, int|string|null $day): Builder
    {
        return filled($day) ? $query->where('day_of_week', $day) : $query;
    }

    public function scopeByType(Builder $query, ?string $type): Builder
    {
        return filled($type)
            ? $query->where(fn (Builder $query): Builder => $query
                ->where('type', $type)
                ->orWhere('lesson_type', $type))
            : $query;
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('day_of_week')->orderBy('starts_at');
    }

    private function syncAliases(): void
    {
        $this->type ??= $this->lesson_type ?? 'theory';
        $this->lesson_type ??= $this->type ?? 'theory';
        $this->start_time ??= $this->starts_at;
        $this->starts_at ??= $this->start_time;
        $this->end_time ??= $this->ends_at;
        $this->ends_at ??= $this->end_time;
    }
}
