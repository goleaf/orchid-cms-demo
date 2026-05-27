<?php

namespace App\Models;

use App\Enums\ReviewStatus;
use App\Models\Concerns\HasTranslations;
use Database\Factories\StudentReviewFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class StudentReview extends Model
{
    /** @use HasFactory<StudentReviewFactory> */
    use HasFactory;

    use HasTranslations;
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'student_profile_id',
        'training_program_id',
        'training_group_id',
        'branch_id',
        'instructor_id',
        'author_name',
        'name_translations',
        'rating',
        'title',
        'body',
        'text_translations',
        'image',
        'video_url',
        'admin_reply',
        'status',
        'is_active',
        'is_featured',
        'published_at',
        'sort_order',
        'created_by_id',
        'updated_by_id',
    ];

    protected $casts = [
        'name_translations' => 'array',
        'text_translations' => 'array',
        'rating' => 'integer',
        'status' => ReviewStatus::class,
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'published_at' => 'datetime',
        'sort_order' => 'integer',
        'deleted_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $review): void {
            if (blank($review->uuid)) {
                $review->uuid = (string) Str::uuid();
            }
        });
    }

    public function studentProfile(): BelongsTo
    {
        return $this->belongsTo(StudentProfile::class);
    }

    public function student(): BelongsTo
    {
        return $this->studentProfile();
    }

    public function trainingProgram(): BelongsTo
    {
        return $this->belongsTo(TrainingProgram::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'training_program_id');
    }

    public function trainingGroup(): BelongsTo
    {
        return $this->belongsTo(TrainingGroup::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(Instructor::class);
    }

    public function displayName(?string $locale = null): string
    {
        return $this->getTranslation('name', $locale)
            ?: $this->author_name
            ?: (string) $this->getKey();
    }

    public function displayText(?string $locale = null): string
    {
        return $this->getTranslation('text', $locale)
            ?: $this->body
            ?: '';
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->active()
            ->where('status', ReviewStatus::Published->value)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderByDesc('published_at');
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->displayName();
    }

    public function getDisplayTitleAttribute(): string
    {
        return $this->title ?: $this->displayName();
    }

    public function scopeForPublicList(Builder $query): Builder
    {
        return $query->select([
            'id',
            'student_profile_id',
            'training_program_id',
            'training_group_id',
            'branch_id',
            'instructor_id',
            'author_name',
            'name_translations',
            'rating',
            'title',
            'body',
            'text_translations',
            'image',
            'video_url',
            'admin_reply',
            'is_active',
            'is_featured',
            'published_at',
            'sort_order',
        ]);
    }
}
