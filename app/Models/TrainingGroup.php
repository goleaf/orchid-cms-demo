<?php

namespace App\Models;

use App\Enums\GroupStatus;
use App\Models\Concerns\HasTranslations;
use Database\Factories\TrainingGroupFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrainingGroup extends Model
{
    /** @use HasFactory<TrainingGroupFactory> */
    use HasFactory;

    use HasTranslations;

    protected $fillable = [
        'branch_id',
        'training_program_id',
        'instructor_id',
        'name',
        'name_translations',
        'code',
        'status',
        'capacity',
        'places_taken',
        'starts_on',
        'ends_on',
        'meeting_days',
        'meeting_time',
        'classroom',
        'is_visible_on_site',
    ];

    protected $casts = [
        'name_translations' => 'array',
        'status' => GroupStatus::class,
        'capacity' => 'integer',
        'places_taken' => 'integer',
        'starts_on' => 'date',
        'ends_on' => 'date',
        'meeting_days' => 'array',
        'meeting_time' => 'datetime:H:i',
        'is_visible_on_site' => 'boolean',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function trainingProgram(): BelongsTo
    {
        return $this->belongsTo(TrainingProgram::class);
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(Instructor::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function seatsAvailable(): int
    {
        $taken = max((int) ($this->enrollments_count ?? 0), $this->places_taken);

        return max(0, $this->capacity - $taken);
    }

    public function displayName(?string $locale = null): string
    {
        return $this->getTranslation('name', $locale)
            ?: $this->name
            ?: $this->code
            ?: (string) $this->getKey();
    }

    public function acceptsPublicApplications(): bool
    {
        return $this->is_visible_on_site
            && in_array($this->status, [
                GroupStatus::Planned,
                GroupStatus::Recruiting,
                GroupStatus::Open,
                GroupStatus::AlmostFull,
            ], true);
    }

    public function scopeVisibleOnSite(Builder $query): Builder
    {
        return $query
            ->where('is_visible_on_site', true)
            ->whereIn('status', [
                GroupStatus::Planned->value,
                GroupStatus::Recruiting->value,
                GroupStatus::Open->value,
                GroupStatus::AlmostFull->value,
            ]);
    }

    public function scopeOperationalList(Builder $query): Builder
    {
        return $query->select([
            'id',
            'branch_id',
            'training_program_id',
            'instructor_id',
            'name',
            'name_translations',
            'code',
            'status',
            'capacity',
            'places_taken',
            'starts_on',
            'ends_on',
            'meeting_days',
            'meeting_time',
            'classroom',
            'is_visible_on_site',
        ]);
    }
}
