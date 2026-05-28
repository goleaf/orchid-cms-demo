<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Database\Factories\StaffProfileFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class StaffProfile extends Model
{
    /** @use HasFactory<StaffProfileFactory> */
    use HasFactory;

    use HasTranslations;
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'user_id',
        'staff_number',
        'branch_id',
        'display_name_translations',
        'job_title_translations',
        'public_bio_translations',
        'phone',
        'work_email',
        'preferred_locale',
        'timezone',
        'avatar',
        'is_visible_on_site',
        'internal_notes',
        'created_by_id',
        'updated_by_id',
    ];

    protected $casts = [
        'display_name_translations' => 'array',
        'job_title_translations' => 'array',
        'public_bio_translations' => 'array',
        'is_visible_on_site' => 'boolean',
        'deleted_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $profile): void {
            if (blank($profile->uuid)) {
                $profile->uuid = (string) Str::uuid();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_id');
    }

    public function scopeVisibleOnSite(Builder $query): Builder
    {
        return $query->where('is_visible_on_site', true);
    }

    public function scopeByBranch(Builder $query, Branch|int|string|null $branch): Builder
    {
        if ($branch === null || $branch === '') {
            return $query;
        }

        $branchId = $branch instanceof Branch ? $branch->getKey() : $branch;

        return $query->where('branch_id', $branchId);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (blank($term)) {
            return $query;
        }

        $like = '%'.str_replace(['%', '_'], ['\%', '\_'], (string) $term).'%';

        return $query->where(function (Builder $query) use ($like): void {
            $query
                ->where('staff_number', 'like', $like)
                ->orWhere('phone', 'like', $like)
                ->orWhere('work_email', 'like', $like)
                ->orWhere('display_name_translations', 'like', $like)
                ->orWhere('job_title_translations', 'like', $like)
                ->orWhereHas('user', fn (Builder $query): Builder => $query
                    ->where('name', 'like', $like)
                    ->orWhere('email', 'like', $like));
        });
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->displayName();
    }

    public function getDisplayJobTitleAttribute(): string
    {
        return $this->displayJobTitle();
    }

    public function displayName(?string $locale = null): string
    {
        $translated = $this->getTranslation('display_name', $locale);

        if (filled($translated)) {
            return (string) $translated;
        }

        if ($this->relationLoaded('user') && filled($this->user?->name)) {
            return (string) $this->user->name;
        }

        return (string) ($this->staff_number ?: $this->getKey());
    }

    public function displayJobTitle(?string $locale = null): string
    {
        return $this->getTranslation('job_title', $locale) ?: '';
    }
}
