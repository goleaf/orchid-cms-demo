<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Database\Factories\TrainingProgramFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class TrainingProgram extends Model
{
    /** @use HasFactory<TrainingProgramFactory> */
    use HasFactory;

    use HasTranslations;
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'course_category_id',
        'code',
        'title',
        'title_translations',
        'name_translations',
        'slug',
        'license_category',
        'transmission',
        'theory_hours',
        'practice_hours',
        'duration_weeks',
        'duration_translations',
        'format',
        'available_languages',
        'price_cents',
        'old_price_cents',
        'price',
        'old_price',
        'currency',
        'description',
        'short_description',
        'short_description_translations',
        'description_translations',
        'program_summary_translations',
        'required_documents',
        'admission_requirements',
        'requirements_translations',
        'included_items',
        'included_items_translations',
        'includes_translations',
        'extra_costs',
        'extra_costs_translations',
        'excludes_translations',
        'theory_program',
        'theory_program_translations',
        'practice_program',
        'practice_program_translations',
        'is_active',
        'is_visible_on_site',
        'is_indexable',
        'is_featured',
        'seo_title',
        'seo_title_translations',
        'meta_description',
        'seo_description_translations',
        'canonical_url',
        'open_graph_image',
        'og_image',
        'image_path',
        'icon',
        'og_title',
        'og_title_translations',
        'og_description',
        'og_description_translations',
        'structured_data',
        'sort_order',
        'created_by_id',
        'updated_by_id',
    ];

    protected $casts = [
        'name_translations' => 'array',
        'title_translations' => 'array',
        'theory_hours' => 'integer',
        'practice_hours' => 'integer',
        'duration_weeks' => 'integer',
        'duration_translations' => 'array',
        'available_languages' => 'array',
        'price_cents' => 'integer',
        'old_price_cents' => 'integer',
        'price' => 'decimal:2',
        'old_price' => 'decimal:2',
        'short_description_translations' => 'array',
        'description_translations' => 'array',
        'program_summary_translations' => 'array',
        'required_documents' => 'array',
        'requirements_translations' => 'array',
        'included_items_translations' => 'array',
        'includes_translations' => 'array',
        'extra_costs_translations' => 'array',
        'excludes_translations' => 'array',
        'theory_program_translations' => 'array',
        'practice_program_translations' => 'array',
        'structured_data' => 'array',
        'seo_title_translations' => 'array',
        'seo_description_translations' => 'array',
        'og_title_translations' => 'array',
        'og_description_translations' => 'array',
        'is_active' => 'boolean',
        'is_visible_on_site' => 'boolean',
        'is_indexable' => 'boolean',
        'is_featured' => 'boolean',
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

    public function category(): BelongsTo
    {
        return $this->belongsTo(CourseCategory::class, 'course_category_id');
    }

    public function modules(): HasMany
    {
        return $this->hasMany(CourseModule::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function examAdmissionRules(): HasMany
    {
        return $this->hasMany(ExamAdmissionRule::class, 'course_id');
    }

    public function examSessions(): HasMany
    {
        return $this->hasMany(ExamSession::class, 'training_program_id');
    }

    public function groups(): HasMany
    {
        return $this->hasMany(TrainingGroup::class, 'training_program_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(StudentReview::class, 'training_program_id');
    }

    public function pricingPackages(): HasMany
    {
        return $this->hasMany(PricingPackage::class, 'course_id');
    }

    public function trainingGroups(): HasMany
    {
        return $this->hasMany(TrainingGroup::class, 'training_program_id');
    }

    public function faqs(): MorphMany
    {
        return $this->morphMany(Faq::class, 'faqable');
    }

    public function testimonials(): HasMany
    {
        return $this->hasMany(Testimonial::class, 'training_program_id');
    }

    public function priceForHumans(): string
    {
        return number_format($this->price_cents / 100, 2).' EUR';
    }

    public function oldPriceForHumans(): ?string
    {
        if ($this->old_price_cents === null) {
            return null;
        }

        return number_format($this->old_price_cents / 100, 2).' EUR';
    }

    public function displayTitle(?string $locale = null): string
    {
        return $this->getTranslation('name', $locale)
            ?: $this->getTranslation('title', $locale)
            ?: $this->title
            ?: $this->license_category
            ?: (string) $this->getKey();
    }

    public function displayShortDescription(?string $locale = null): ?string
    {
        return $this->getTranslation('short_description', $locale)
            ?: $this->short_description
            ?: $this->displayDescription($locale);
    }

    public function displayDescription(?string $locale = null): ?string
    {
        return $this->getTranslation('description', $locale)
            ?: $this->description;
    }

    public function displayIncludedItems(?string $locale = null): ?string
    {
        return $this->getTranslation('includes', $locale)
            ?: $this->getTranslation('included_items', $locale)
            ?: $this->included_items;
    }

    public function displayExtraCosts(?string $locale = null): ?string
    {
        return $this->getTranslation('excludes', $locale)
            ?: $this->getTranslation('extra_costs', $locale)
            ?: $this->extra_costs;
    }

    public function displayRequirements(?string $locale = null): ?string
    {
        return $this->getTranslation('requirements', $locale)
            ?: $this->admission_requirements;
    }

    public function displayTheoryProgram(?string $locale = null): ?string
    {
        return $this->getTranslation('theory_program', $locale)
            ?: $this->theory_program;
    }

    public function displayPracticeProgram(?string $locale = null): ?string
    {
        return $this->getTranslation('practice_program', $locale)
            ?: $this->practice_program;
    }

    public function displaySeoTitle(?string $locale = null): string
    {
        return $this->getTranslation('seo_title', $locale)
            ?: ($this->attributes['seo_title'] ?? null)
            ?: $this->displayTitle($locale);
    }

    public function displaySeoDescription(?string $locale = null): ?string
    {
        return $this->getTranslation('seo_description', $locale)
            ?: ($this->attributes['meta_description'] ?? null)
            ?: $this->displayShortDescription($locale);
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

    public function getRouteKeyName(): string
    {
        return 'slug';
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

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('title');
    }

    public function scopeBySlug(Builder $query, string $slug): Builder
    {
        return $query->where('slug', $slug);
    }

    public function scopeForAcademyList(Builder $query): Builder
    {
        return $query->select([
            'id',
            'title',
            'title_translations',
            'name_translations',
            'slug',
            'license_category',
            'transmission',
            'theory_hours',
            'practice_hours',
            'duration_weeks',
            'duration_translations',
            'format',
            'available_languages',
            'price_cents',
            'old_price_cents',
            'description',
            'short_description',
            'short_description_translations',
            'description_translations',
            'program_summary_translations',
            'required_documents',
            'admission_requirements',
            'requirements_translations',
            'included_items',
            'included_items_translations',
            'includes_translations',
            'extra_costs',
            'extra_costs_translations',
            'excludes_translations',
            'theory_program',
            'theory_program_translations',
            'practice_program',
            'practice_program_translations',
            'is_active',
            'is_visible_on_site',
            'is_indexable',
            'seo_title',
            'seo_title_translations',
            'meta_description',
            'seo_description_translations',
            'canonical_url',
            'open_graph_image',
            'og_image',
            'image_path',
            'og_title',
            'og_title_translations',
            'og_description',
            'og_description_translations',
            'structured_data',
            'sort_order',
            'updated_at',
        ]);
    }
}
