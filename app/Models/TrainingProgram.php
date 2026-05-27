<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Database\Factories\TrainingProgramFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrainingProgram extends Model
{
    /** @use HasFactory<TrainingProgramFactory> */
    use HasFactory;

    use HasTranslations;

    protected $fillable = [
        'title',
        'title_translations',
        'slug',
        'license_category',
        'transmission',
        'theory_hours',
        'practice_hours',
        'duration_weeks',
        'format',
        'available_languages',
        'price_cents',
        'old_price_cents',
        'description',
        'short_description',
        'short_description_translations',
        'description_translations',
        'required_documents',
        'admission_requirements',
        'included_items',
        'included_items_translations',
        'extra_costs',
        'extra_costs_translations',
        'theory_program',
        'theory_program_translations',
        'practice_program',
        'practice_program_translations',
        'is_active',
        'seo_title',
        'seo_title_translations',
        'meta_description',
        'seo_description_translations',
        'canonical_url',
        'open_graph_image',
        'image_path',
        'og_title',
        'og_title_translations',
        'og_description',
        'og_description_translations',
        'structured_data',
        'sort_order',
    ];

    protected $casts = [
        'title_translations' => 'array',
        'theory_hours' => 'integer',
        'practice_hours' => 'integer',
        'duration_weeks' => 'integer',
        'available_languages' => 'array',
        'price_cents' => 'integer',
        'old_price_cents' => 'integer',
        'short_description_translations' => 'array',
        'description_translations' => 'array',
        'required_documents' => 'array',
        'included_items_translations' => 'array',
        'extra_costs_translations' => 'array',
        'theory_program_translations' => 'array',
        'practice_program_translations' => 'array',
        'structured_data' => 'array',
        'seo_title_translations' => 'array',
        'seo_description_translations' => 'array',
        'og_title_translations' => 'array',
        'og_description_translations' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function modules(): HasMany
    {
        return $this->hasMany(CourseModule::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function groups(): HasMany
    {
        return $this->hasMany(TrainingGroup::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(StudentReview::class);
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
        return $this->getTranslation('title', $locale)
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
        return $this->getTranslation('included_items', $locale)
            ?: $this->included_items;
    }

    public function displayExtraCosts(?string $locale = null): ?string
    {
        return $this->getTranslation('extra_costs', $locale)
            ?: $this->extra_costs;
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
            ?: $this->seo_title
            ?: $this->displayTitle($locale);
    }

    public function displaySeoDescription(?string $locale = null): ?string
    {
        return $this->getTranslation('seo_description', $locale)
            ?: $this->meta_description
            ?: $this->displayShortDescription($locale);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForAcademyList(Builder $query): Builder
    {
        return $query->select([
            'id',
            'title',
            'title_translations',
            'slug',
            'license_category',
            'transmission',
            'theory_hours',
            'practice_hours',
            'duration_weeks',
            'format',
            'available_languages',
            'price_cents',
            'old_price_cents',
            'description',
            'short_description',
            'short_description_translations',
            'description_translations',
            'required_documents',
            'admission_requirements',
            'included_items',
            'included_items_translations',
            'extra_costs',
            'extra_costs_translations',
            'theory_program',
            'theory_program_translations',
            'practice_program',
            'practice_program_translations',
            'is_active',
            'seo_title',
            'seo_title_translations',
            'meta_description',
            'seo_description_translations',
            'canonical_url',
            'open_graph_image',
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
