<?php

namespace App\Rules;

use App\Models\ExamType;
use App\Models\StudentEnrollment;
use App\Services\Exams\ExamWorkflowService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Throwable;

class InternalExamPassedRule implements ValidationRule
{
    public function __construct(private readonly ExamType|int|string|null $type = null) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! filled($value)) {
            return;
        }

        $enrollment = $value instanceof StudentEnrollment ? $value : StudentEnrollment::query()->find($value);

        try {
            if ($enrollment !== null && app(ExamWorkflowService::class)->internalExamPassed($enrollment, $this->type)) {
                return;
            }
        } catch (Throwable) {
        }

        $fail(tkey('exams.validation.internal_exam_passed'));
    }
}
