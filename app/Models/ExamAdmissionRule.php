<?php

namespace App\Models;

use Database\Factories\ExamAdmissionRuleFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamAdmissionRule extends Model
{
    /** @use HasFactory<ExamAdmissionRuleFactory> */
    use HasFactory;

    protected $fillable = [
        'exam_type_id',
        'course_id',
        'course_category_id',
        'required_theory_hours',
        'required_practice_hours',
        'require_documents',
        'require_no_debt',
        'require_internal_exam_passed',
        'is_active',
    ];

    protected $casts = [
        'required_theory_hours' => 'decimal:2',
        'required_practice_hours' => 'decimal:2',
        'require_documents' => 'boolean',
        'require_no_debt' => 'boolean',
        'require_internal_exam_passed' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function examType(): BelongsTo
    {
        return $this->belongsTo(ExamType::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function trainingProgram(): BelongsTo
    {
        return $this->belongsTo(TrainingProgram::class, 'course_id');
    }

    public function courseCategory(): BelongsTo
    {
        return $this->belongsTo(CourseCategory::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForExamType(Builder $query, ExamType|int|string|null $examType): Builder
    {
        if ($examType instanceof ExamType) {
            return $query->where('exam_type_id', $examType->getKey());
        }

        return filled($examType)
            ? $query->where('exam_type_id', $examType)
            : $query;
    }

    public function scopeForCourse(Builder $query, TrainingProgram|int|string|null $course): Builder
    {
        if ($course instanceof TrainingProgram) {
            return $query->where('course_id', $course->getKey());
        }

        return filled($course)
            ? $query->where('course_id', $course)
            : $query;
    }

    public function scopeForCourseCategory(Builder $query, CourseCategory|int|string|null $category): Builder
    {
        if ($category instanceof CourseCategory) {
            return $query->where('course_category_id', $category->getKey());
        }

        return filled($category)
            ? $query->where('course_category_id', $category)
            : $query;
    }

    public function requiredHoursSummary(): string
    {
        return collect([
            $this->required_theory_hours !== null ? 'theory '.$this->required_theory_hours : null,
            $this->required_practice_hours !== null ? 'practice '.$this->required_practice_hours : null,
        ])->filter()->implode(', ');
    }
}
