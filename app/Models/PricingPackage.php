<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Database\Factories\PricingPackageFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class PricingPackage extends Model
{
    /** @use HasFactory<PricingPackageFactory> */
    use HasFactory;

    use HasTranslations;
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'course_id',
        'course_category_id',
        'code',
        'slug',
        'name_translations',
        'description_translations',
        'features_translations',
        'price',
        'old_price',
        'currency',
        'theory_hours',
        'practice_hours',
        'is_active',
        'is_visible_on_site',
        'is_featured',
        'sort_order',
        'created_by_id',
        'updated_by_id',
    ];

    protected $casts = [
        'name_translations' => 'array',
        'description_translations' => 'array',
        'features_translations' => 'array',
        'price' => 'decimal:2',
        'old_price' => 'decimal:2',
        'theory_hours' => 'decimal:2',
        'practice_hours' => 'decimal:2',
        'is_active' => 'boolean',
        'is_visible_on_site' => 'boolean',
        'is_featured' => 'boolean',
        'sort_order' => 'integer',
        'deleted_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $package): void {
            if (blank($package->uuid)) {
                $package->uuid = (string) Str::uuid();
            }
        });
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(CourseCategory::class, 'course_category_id');
    }

    public function displayName(?string $locale = null): string
    {
        return $this->getTranslation('name', $locale)
            ?: $this->code
            ?: $this->slug
            ?: (string) $this->getKey();
    }

    public function displayDescription(?string $locale = null): ?string
    {
        return $this->getTranslation('description', $locale);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeVisibleOnSite(Builder $query): Builder
    {
        return $query->where('is_visible_on_site', true);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('slug');
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->displayName();
    }

    public function getDisplayTitleAttribute(): string
    {
        return $this->displayName();
    }
}
