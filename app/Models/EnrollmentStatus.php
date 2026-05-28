<?php

namespace App\Models;

use App\Models\Concerns\HasTranslatedDictionaryName;
use Database\Factories\EnrollmentStatusFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EnrollmentStatus extends Model
{
    /** @use HasFactory<EnrollmentStatusFactory> */
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
        'is_success',
        'is_cancelled',
        'is_waiting_documents',
        'is_waiting_payment',
        'is_in_progress',
    ];

    protected $casts = [
        'name_translations' => 'array',
        'description_translations' => 'array',
        'sort_order' => 'integer',
        'is_system' => 'boolean',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'is_final' => 'boolean',
        'is_success' => 'boolean',
        'is_cancelled' => 'boolean',
        'is_waiting_documents' => 'boolean',
        'is_waiting_payment' => 'boolean',
        'is_in_progress' => 'boolean',
    ];

    public function enrollments(): HasMany
    {
        return $this->hasMany(StudentEnrollment::class, 'status_id');
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->displayName();
    }
}
