<?php

namespace App\Models;

use App\Models\Concerns\HasTranslatedDictionaryName;
use Database\Factories\TrainingGroupStatusFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrainingGroupStatus extends Model
{
    /** @use HasFactory<TrainingGroupStatusFactory> */
    use HasFactory;

    use HasTranslatedDictionaryName;

    public const DICTIONARY_KEY_COLUMN = 'code';

    protected $fillable = [
        'code',
        'name',
        'name_translations',
        'description_translations',
        'color',
        'sort_order',
        'is_system',
        'is_default',
        'is_active',
        'is_public',
        'accepts_enrollments',
        'is_in_progress',
        'is_final',
        'is_success',
        'is_cancelled',
    ];

    protected $casts = [
        'name_translations' => 'array',
        'description_translations' => 'array',
        'sort_order' => 'integer',
        'is_system' => 'boolean',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'is_public' => 'boolean',
        'accepts_enrollments' => 'boolean',
        'is_in_progress' => 'boolean',
        'is_final' => 'boolean',
        'is_success' => 'boolean',
        'is_cancelled' => 'boolean',
    ];

    public function groups(): HasMany
    {
        return $this->hasMany(TrainingGroup::class, 'status_id');
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->displayName();
    }
}
