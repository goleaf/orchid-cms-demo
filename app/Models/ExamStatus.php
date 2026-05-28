<?php

namespace App\Models;

use App\Models\Concerns\HasTranslatedDictionaryName;
use Database\Factories\ExamStatusFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamStatus extends Model
{
    /** @use HasFactory<ExamStatusFactory> */
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
        'is_active',
    ];

    protected $casts = [
        'name_translations' => 'array',
        'description_translations' => 'array',
        'sort_order' => 'integer',
        'is_system' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function sessions(): HasMany
    {
        return $this->hasMany(ExamSession::class, 'status_id');
    }

    public function scopeByCode(Builder $query, string $code): Builder
    {
        return $query->where('code', $code);
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereIn('code', ['scheduled', 'open', 'in_progress']);
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->displayName();
    }
}
