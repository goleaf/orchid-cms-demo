<?php

namespace App\Rules;

use App\Enums\EnrollmentStatus;
use App\Models\MarketingLead;
use App\Models\Student;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class EnrollmentNotDuplicateForStudentRule implements ValidationRule
{
    /**
     * @param  array<string, mixed>  $enrollmentData
     */
    public function __construct(
        private readonly ?Student $student = null,
        private readonly ?MarketingLead $lead = null,
        private readonly array $enrollmentData = [],
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $student = $this->student;

        if ($student === null) {
            return;
        }

        $data = is_array($value) ? array_replace($this->enrollmentData, $value) : $this->enrollmentData;
        $courseId = $data['training_program_id'] ?? $data['course_id'] ?? $this->lead?->training_program_id;
        $groupId = $data['training_group_id'] ?? $this->lead?->training_group_id;

        if (blank($courseId) && blank($groupId)) {
            return;
        }

        $exists = $student->enrollments()
            ->whereNotIn('status', [
                EnrollmentStatus::Completed->value,
                EnrollmentStatus::Cancelled->value,
                EnrollmentStatus::Archived->value,
            ])
            ->when(filled($courseId), fn ($query) => $query->where('training_program_id', $courseId))
            ->when(filled($groupId), fn ($query) => $query->where('training_group_id', $groupId))
            ->exists();

        if ($exists) {
            $fail(tkey('students.conversion.validation.duplicate_enrollment'));
        }
    }
}
