<?php

namespace App\Models;

use App\Models\Concerns\HasTranslatedDictionaryName;
use Illuminate\Database\Eloquent\Model;

class LeadStatus extends Model
{
    use HasTranslatedDictionaryName;

    public const DICTIONARY_KEY_COLUMN = 'code';

    protected $fillable = [
        'code',
        'name',
        'name_translations',
        'color',
        'is_system',
        'is_active',
        'is_default',
        'is_final',
        'is_success',
        'is_lost',
        'sort_order',
    ];

    protected $casts = [
        'name_translations' => 'array',
        'is_system' => 'boolean',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'is_final' => 'boolean',
        'is_success' => 'boolean',
        'is_lost' => 'boolean',
        'sort_order' => 'integer',
    ];
}
