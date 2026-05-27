<?php

namespace App\Models;

use Database\Factories\TrainingProgramFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrainingProgram extends Model
{
    /** @use HasFactory<TrainingProgramFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'license_category',
        'transmission',
        'theory_hours',
        'practice_hours',
        'price_cents',
        'description',
        'is_active',
    ];

    protected $casts = [
        'theory_hours' => 'integer',
        'practice_hours' => 'integer',
        'price_cents' => 'integer',
        'is_active' => 'boolean',
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

    public function priceForHumans(): string
    {
        return number_format($this->price_cents / 100, 2).' EUR';
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
            'slug',
            'license_category',
            'transmission',
            'theory_hours',
            'practice_hours',
            'price_cents',
            'is_active',
        ]);
    }
}
