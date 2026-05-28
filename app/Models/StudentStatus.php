<?php

namespace App\Models;

use App\Models\Concerns\HasTranslatedDictionaryName;
use Database\Factories\StudentStatusFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudentStatus extends Model
{
    /** @use HasFactory<StudentStatusFactory> */
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
        'is_final',
        'is_blocked',
        'is_archived',
    ];

    protected $casts = [
        'name_translations' => 'array',
        'description_translations' => 'array',
        'sort_order' => 'integer',
        'is_system' => 'boolean',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'is_final' => 'boolean',
        'is_blocked' => 'boolean',
        'is_archived' => 'boolean',
    ];

    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'status_id');
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->displayName();
    }
}
