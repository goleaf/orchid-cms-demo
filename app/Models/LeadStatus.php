<?php

namespace App\Models;

use App\Models\Concerns\HasTranslatedDictionaryName;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeadStatus extends Model
{
    use HasFactory;
    use HasTranslatedDictionaryName;

    public const DICTIONARY_KEY_COLUMN = 'code';

    protected $fillable = [
        'code',
        'name',
        'name_translations',
        'description_translations',
        'color',
        'is_system',
        'is_active',
        'is_public',
        'is_default',
        'is_final',
        'is_success',
        'is_lost',
        'is_duplicate',
        'is_spam',
        'sort_order',
    ];

    protected $casts = [
        'name_translations' => 'array',
        'description_translations' => 'array',
        'is_system' => 'boolean',
        'is_active' => 'boolean',
        'is_public' => 'boolean',
        'is_default' => 'boolean',
        'is_final' => 'boolean',
        'is_success' => 'boolean',
        'is_lost' => 'boolean',
        'is_duplicate' => 'boolean',
        'is_spam' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function leads(): HasMany
    {
        return $this->hasMany(MarketingLead::class, 'status', 'code');
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->displayName();
    }
}
