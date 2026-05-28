<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Database\Factories\BranchFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Branch extends Model
{
    /** @use HasFactory<BranchFactory> */
    use HasFactory;

    use HasTranslations;
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'code',
        'name',
        'name_translations',
        'slug',
        'city',
        'city_translations',
        'address',
        'address_translations',
        'phone',
        'email',
        'description',
        'description_translations',
        'working_hours',
        'working_hours_translations',
        'latitude',
        'longitude',
        'map_url',
        'image',
        'seo_title',
        'seo_title_translations',
        'seo_description',
        'seo_description_translations',
        'canonical_url',
        'open_graph_image',
        'og_title',
        'og_title_translations',
        'og_description',
        'og_description_translations',
        'is_active',
        'is_visible_on_site',
        'is_indexable',
        'sort_order',
        'created_by_id',
        'updated_by_id',
    ];

    protected $casts = [
        'name_translations' => 'array',
        'city_translations' => 'array',
        'address_translations' => 'array',
        'description_translations' => 'array',
        'working_hours_translations' => 'array',
        'seo_title_translations' => 'array',
        'seo_description_translations' => 'array',
        'og_title_translations' => 'array',
        'og_description_translations' => 'array',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'is_active' => 'boolean',
        'is_visible_on_site' => 'boolean',
        'is_indexable' => 'boolean',
        'sort_order' => 'integer',
        'deleted_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $branch): void {
            if (blank($branch->uuid)) {
                $branch->uuid = (string) Str::uuid();
            }
        });
    }

    public function instructors(): HasMany
    {
        return $this->hasMany(Instructor::class);
    }

    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(StudentProfile::class);
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(DrivingLesson::class);
    }

    public function examSessions(): HasMany
    {
        return $this->hasMany(ExamSession::class);
    }

    public function groups(): HasMany
    {
        return $this->hasMany(TrainingGroup::class);
    }

    public function trainingGroups(): HasMany
    {
        return $this->groups();
    }

    public function campaigns(): HasMany
    {
        return $this->hasMany(MarketingCampaign::class);
    }

    public function leads(): HasMany
    {
        return $this->hasMany(MarketingLead::class);
    }

    public function faqs(): MorphMany
    {
        return $this->morphMany(Faq::class, 'faqable');
    }

    public function testimonials(): HasMany
    {
        return $this->hasMany(Testimonial::class);
    }

    public function scopeForAdminList(Builder $query): Builder
    {
        return $query->select([
            'id',
            'name',
            'name_translations',
            'slug',
            'city',
            'city_translations',
            'address',
            'address_translations',
            'phone',
            'email',
            'description',
            'description_translations',
            'working_hours',
            'working_hours_translations',
            'latitude',
            'longitude',
            'map_url',
            'image',
            'seo_title',
            'seo_title_translations',
            'seo_description',
            'seo_description_translations',
            'canonical_url',
            'open_graph_image',
            'og_title',
            'og_title_translations',
            'og_description',
            'og_description_translations',
            'is_active',
            'is_visible_on_site',
            'is_indexable',
            'sort_order',
        ]);
    }

    public function scopePublic(Builder $query): Builder
    {
        return $query
            ->forAdminList()
            ->active()
            ->visibleOnSite();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeVisibleOnSite(Builder $query): Builder
    {
        return $query->where('is_visible_on_site', true);
    }

    public function scopeIndexable(Builder $query): Builder
    {
        return $query->where('is_indexable', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function scopeBySlug(Builder $query, string $slug): Builder
    {
        return $query->where('slug', $slug);
    }

    public function displayName(?string $locale = null): string
    {
        return $this->getTranslation('name', $locale)
            ?: $this->name
            ?: (string) $this->getKey();
    }

    public function displayCity(?string $locale = null): string
    {
        return $this->getTranslation('city', $locale)
            ?: $this->city
            ?: '';
    }

    public function displayAddress(?string $locale = null): string
    {
        return $this->getTranslation('address', $locale)
            ?: $this->address
            ?: '';
    }

    public function displayDescription(?string $locale = null): ?string
    {
        return $this->getTranslation('description', $locale)
            ?: $this->description;
    }

    public function displayWorkingHours(?string $locale = null): ?string
    {
        return $this->getTranslation('working_hours', $locale)
            ?: $this->working_hours;
    }

    public function displaySeoTitle(?string $locale = null): string
    {
        return $this->getTranslation('seo_title', $locale)
            ?: ($this->attributes['seo_title'] ?? null)
            ?: $this->displayName($locale);
    }

    public function displaySeoDescription(?string $locale = null): ?string
    {
        return $this->getTranslation('seo_description', $locale)
            ?: ($this->attributes['seo_description'] ?? null)
            ?: $this->displayDescription($locale);
    }

    public function displayOgTitle(?string $locale = null): string
    {
        return $this->getTranslation('og_title', $locale)
            ?: ($this->attributes['og_title'] ?? null)
            ?: $this->displaySeoTitle($locale);
    }

    public function displayOgDescription(?string $locale = null): ?string
    {
        return $this->getTranslation('og_description', $locale)
            ?: ($this->attributes['og_description'] ?? null)
            ?: $this->displaySeoDescription($locale);
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
