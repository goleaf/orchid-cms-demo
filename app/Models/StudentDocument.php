<?php

namespace App\Models;

use App\Enums\DocumentStatus;
use Database\Factories\StudentDocumentFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudentDocument extends Model
{
    /** @use HasFactory<StudentDocumentFactory> */
    use HasFactory;

    protected $fillable = [
        'student_profile_id',
        'enrollment_id',
        'document_type',
        'status',
        'title',
        'number',
        'issued_at',
        'expires_at',
        'file_path',
        'notes',
    ];

    protected $casts = [
        'status' => DocumentStatus::class,
        'issued_at' => 'date',
        'expires_at' => 'date',
    ];

    public function studentProfile(): BelongsTo
    {
        return $this->belongsTo(StudentProfile::class);
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function examChecklistItems(): HasMany
    {
        return $this->hasMany(ExamAdmissionChecklistItem::class);
    }

    public function examAttempts(): HasMany
    {
        return $this->hasMany(ExamAttempt::class);
    }

    public function scopeForDocumentList(Builder $query): Builder
    {
        return $query->select([
            'id',
            'student_profile_id',
            'enrollment_id',
            'document_type',
            'status',
            'title',
            'number',
            'issued_at',
            'expires_at',
        ]);
    }
}
