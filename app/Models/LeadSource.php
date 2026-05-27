<?php

namespace App\Models;

use App\Models\Concerns\HasTranslatedDictionaryName;
use Illuminate\Database\Eloquent\Model;

class LeadSource extends Model
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
        'sort_order',
    ];

    protected $casts = [
        'name_translations' => 'array',
        'is_system' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];
}
