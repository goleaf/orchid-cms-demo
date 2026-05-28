<?php

namespace App\Models;

use App\Enums\SitePageType;
use App\Models\Concerns\HasTranslations;
use Database\Factories\SitePageFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class SitePage extends Model
{
    /** @use HasFactory<SitePageFactory> */
    use HasFactory;

    use HasTranslations;
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'type',
        'slug',
        'title_translations',
        'subtitle_translations',
        'content_translations',
        'excerpt_translations',
        'seo_title_translations',
        'seo_description_translations',
        'og_title_translations',
        'og_description_translations',
        'og_image',
        'canonical_url',
        'template',
        'is_active',
        'is_indexable',
        'sort_order',
        'published_at',
        'created_by_id',
        'updated_by_id',
    ];

    protected $casts = [
        'title_translations' => 'array',
        'subtitle_translations' => 'array',
        'content_translations' => 'array',
        'excerpt_translations' => 'array',
        'seo_title_translations' => 'array',
        'seo_description_translations' => 'array',
        'og_title_translations' => 'array',
        'og_description_translations' => 'array',
        'is_active' => 'boolean',
        'is_indexable' => 'boolean',
        'sort_order' => 'integer',
        'published_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $page): void {
            if (blank($page->uuid)) {
                $page->uuid = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function displayTitle(?string $locale = null): string
    {
        return $this->getTranslation('title', $locale)
            ?: $this->slug
            ?: (string) $this->getKey();
    }

    public function displayContent(?string $locale = null): ?string
    {
        return $this->getTranslation('content', $locale);
    }

    public function displaySeoTitle(?string $locale = null): string
    {
        return $this->getTranslation('seo_title', $locale)
            ?: $this->displayTitle($locale);
    }

    public function displaySeoDescription(?string $locale = null): ?string
    {
        return $this->getTranslation('seo_description', $locale)
            ?: $this->getTranslation('excerpt', $locale);
    }

    public function displayOgTitle(?string $locale = null): string
    {
        return $this->getTranslation('og_title', $locale)
            ?: $this->displaySeoTitle($locale);
    }

    public function displayOgDescription(?string $locale = null): ?string
    {
        return $this->getTranslation('og_description', $locale)
            ?: $this->displaySeoDescription($locale);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function scopeBySlug(Builder $query, string $slug): Builder
    {
        return $query->where('slug', $slug);
    }

    public function scopeIndexable(Builder $query): Builder
    {
        return $query->where('is_indexable', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('slug');
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->displayTitle();
    }

    public function getDisplayTitleAttribute(): string
    {
        return $this->displayTitle();
    }

    public function getSeoTitleAttribute(): string
    {
        return $this->displaySeoTitle();
    }

    public function getSeoDescriptionAttribute(): ?string
    {
        return $this->displaySeoDescription();
    }

    /**
     * @return array<int, string>
     */
    public static function typeValues(): array
    {
        return SitePageType::values();
    }
}
