<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Database\Factories\BranchFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Branch extends Model
{
    /** @use HasFactory<BranchFactory> */
    use HasFactory;

    use HasTranslations;

    protected $fillable = [
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
        'sort_order',
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
        'sort_order' => 'integer',
    ];

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

    public function groups(): HasMany
    {
        return $this->hasMany(TrainingGroup::class);
    }

    public function campaigns(): HasMany
    {
        return $this->hasMany(MarketingCampaign::class);
    }

    public function leads(): HasMany
    {
        return $this->hasMany(MarketingLead::class);
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
            'sort_order',
        ]);
    }

    public function scopePublic(Builder $query): Builder
    {
        return $query
            ->forAdminList()
            ->where('is_active', true);
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
            ?: $this->seo_title
            ?: $this->displayName($locale);
    }

    public function displaySeoDescription(?string $locale = null): ?string
    {
        return $this->getTranslation('seo_description', $locale)
            ?: $this->seo_description
            ?: $this->displayDescription($locale);
    }
}
