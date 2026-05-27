<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Database\Factories\CourseCategoryFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class CourseCategory extends Model
{
    /** @use HasFactory<CourseCategoryFactory> */
    use HasFactory;

    use HasTranslations;
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'code',
        'slug',
        'name_translations',
        'description_translations',
        'short_description_translations',
        'seo_title_translations',
        'seo_description_translations',
        'image',
        'icon',
        'is_active',
        'is_visible_on_site',
        'sort_order',
        'created_by_id',
        'updated_by_id',
    ];

    protected $casts = [
        'name_translations' => 'array',
        'description_translations' => 'array',
        'short_description_translations' => 'array',
        'seo_title_translations' => 'array',
        'seo_description_translations' => 'array',
        'is_active' => 'boolean',
        'is_visible_on_site' => 'boolean',
        'sort_order' => 'integer',
        'deleted_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $category): void {
            if (blank($category->uuid)) {
                $category->uuid = (string) Str::uuid();
            }
        });
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class, 'course_category_id');
    }

    public function pricingPackages(): HasMany
    {
        return $this->hasMany(PricingPackage::class);
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

    public function displaySeoTitle(?string $locale = null): string
    {
        return $this->getTranslation('seo_title', $locale)
            ?: $this->displayName($locale);
    }

    public function displaySeoDescription(?string $locale = null): ?string
    {
        return $this->getTranslation('seo_description', $locale)
            ?: $this->getTranslation('short_description', $locale)
            ?: $this->displayDescription($locale);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeVisibleOnSite(Builder $query): Builder
    {
        return $query->where('is_visible_on_site', true);
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

    public function getSeoTitleAttribute(): string
    {
        return $this->displaySeoTitle();
    }

    public function getSeoDescriptionAttribute(): ?string
    {
        return $this->displaySeoDescription();
    }
}
