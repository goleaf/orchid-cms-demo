<?php

namespace App\Models;

use App\Enums\AnalyticsDashboardAudience;
use App\Models\Concerns\HasTranslations;
use Database\Factories\AnalyticsDashboardFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class AnalyticsDashboard extends Model
{
    /** @use HasFactory<AnalyticsDashboardFactory> */
    use HasFactory;

    use HasTranslations;
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'code',
        'name_translations',
        'description_translations',
        'audience',
        'is_active',
        'is_default',
        'sort_order',
        'created_by_id',
        'updated_by_id',
    ];

    protected $casts = [
        'name_translations' => 'array',
        'description_translations' => 'array',
        'audience' => AnalyticsDashboardAudience::class,
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'sort_order' => 'integer',
        'deleted_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $dashboard): void {
            if (blank($dashboard->uuid)) {
                $dashboard->uuid = (string) Str::uuid();
            }
        });
    }

    public function widgets(): HasMany
    {
        return $this->hasMany(DashboardWidget::class);
    }

    public function preferences(): HasMany
    {
        return $this->hasMany(UserDashboardPreference::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_id');
    }

    public function displayName(?string $locale = null): string
    {
        return $this->getTranslation('name', $locale)
            ?: str($this->code)->replace(['.', '_', '-'], ' ')->title()->toString();
    }

    public function displayDescription(?string $locale = null): ?string
    {
        return $this->getTranslation('description', $locale);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeDefault(Builder $query): Builder
    {
        return $query->where('is_default', true);
    }

    public function scopeForAudience(Builder $query, AnalyticsDashboardAudience|string $audience): Builder
    {
        $value = $audience instanceof AnalyticsDashboardAudience ? $audience->value : $audience;

        return $query->where('audience', $value);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('code');
    }
}
