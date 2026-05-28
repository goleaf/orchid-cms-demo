<?php

namespace App\Rules;

use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Services\Exams\ExamWorkflowService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class StudentActiveForExamRule implements ValidationRule
{
    public function __construct(private readonly bool $valueIsEnrollment = false) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! filled($value)) {
            return;
        }

        $subject = $value instanceof Student || $value instanceof StudentEnrollment
            ? $value
            : ($this->valueIsEnrollment
                ? StudentEnrollment::query()->find($value)
                : Student::query()->find($value));

        if ($subject === null || ! app(ExamWorkflowService::class)->studentActiveForExam($subject)) {
            $fail(tkey('exams.validation.student_inactive'));
        }
    }
}
