<?php

namespace App\Models;

use App\Enums\ExamChecklistItemStatus;
use App\Models\Concerns\HasTranslations;
use Database\Factories\ExamChecklistItemFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamChecklistItem extends Model
{
    /** @use HasFactory<ExamChecklistItemFactory> */
    use HasFactory;

    use HasTranslations;

    protected $fillable = [
        'exam_session_id',
        'attempt_id',
        'student_id',
        'enrollment_id',
        'key',
        'title_translations',
        'status',
        'required',
        'passed',
        'message_key',
        'checked_at',
        'checked_by',
    ];

    protected $casts = [
        'title_translations' => 'array',
        'required' => 'boolean',
        'passed' => 'boolean',
        'checked_at' => 'datetime',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(ExamSession::class, 'exam_session_id');
    }

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(ExamAttempt::class, 'attempt_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(StudentEnrollment::class, 'enrollment_id');
    }

    public function checkedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_by');
    }

    public function displayTitle(?string $locale = null): string
    {
        return $this->getTranslation('title', $locale)
            ?: tkey('exams.checklist.items.'.$this->key)
            ?: $this->key;
    }

    public function scopeRequired(Builder $query): Builder
    {
        return $query->where('required', true);
    }

    public function scopeOptional(Builder $query): Builder
    {
        return $query->where('required', false);
    }

    public function scopePassed(Builder $query): Builder
    {
        return $query->where('status', ExamChecklistItemStatus::Passed->value);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', ExamChecklistItemStatus::Pending->value);
    }

    public function getDisplayTitleAttribute(): string
    {
        return $this->displayTitle();
    }
}
