<?php

namespace App\Models;

use App\Models\Concerns\HasTranslatedDictionaryName;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class LeadTag extends Model
{
    use HasFactory;
    use HasTranslatedDictionaryName;

    public const DICTIONARY_KEY_COLUMN = 'slug';

    protected $fillable = [
        'slug',
        'name',
        'name_translations',
        'description_translations',
        'color',
        'is_system',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'name_translations' => 'array',
        'description_translations' => 'array',
        'is_system' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function leads(): BelongsToMany
    {
        return $this->belongsToMany(MarketingLead::class, 'lead_tag_marketing_lead')
            ->withTimestamps();
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->displayName();
    }
}
