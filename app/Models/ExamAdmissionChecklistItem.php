<?php

namespace App\Models;

use App\Enums\ExamChecklistItemStatus;
use App\Models\Concerns\HasTranslations;
use Database\Factories\ExamAdmissionChecklistItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamAdmissionChecklistItem extends Model
{
    /** @use HasFactory<ExamAdmissionChecklistItemFactory> */
    use HasFactory;

    use HasTranslations;

    protected $fillable = [
        'exam_admission_id',
        'code',
        'title_translations',
        'status',
        'source_type',
        'source_id',
        'student_document_id',
        'payment_id',
        'driving_lesson_id',
        'checked_at',
        'checked_by_id',
        'notes',
        'meta',
    ];

    protected $casts = [
        'title_translations' => 'array',
        'status' => ExamChecklistItemStatus::class,
        'checked_at' => 'datetime',
        'meta' => 'array',
    ];

    public function admission(): BelongsTo
    {
        return $this->belongsTo(ExamAdmission::class, 'exam_admission_id');
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(StudentDocument::class, 'student_document_id');
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function drivingLesson(): BelongsTo
    {
        return $this->belongsTo(DrivingLesson::class);
    }

    public function checkedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_by_id');
    }

    public function displayTitle(?string $locale = null): string
    {
        return $this->getTranslation('title', $locale)
            ?: tkey('exams.checklist.items.'.$this->code)
            ?: $this->code;
    }
}
