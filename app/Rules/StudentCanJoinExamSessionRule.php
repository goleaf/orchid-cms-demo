<?php

namespace App\Rules;

use App\Models\ExamSession;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Services\Exams\ExamWorkflowService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class StudentCanJoinExamSessionRule implements ValidationRule
{
    public function __construct(
        private readonly ?ExamSession $session = null,
        private readonly ?StudentEnrollment $enrollment = null,
        private readonly bool $allowOverbooking = false,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($this->session === null || $this->enrollment === null || ! filled($value)) {
            return;
        }

        $student = $value instanceof Student ? $value : Student::query()->find($value);

        if ($student === null || ! app(ExamWorkflowService::class)->studentCanJoinSession($this->session, $student, $this->enrollment, $this->allowOverbooking)) {
            $fail(tkey('exams.validation.student_cannot_join_session'));
        }
    }
}
