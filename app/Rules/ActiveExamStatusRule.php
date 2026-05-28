<?php

namespace App\Rules;

use App\Services\Exams\ExamWorkflowService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ActiveExamStatusRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! filled($value)) {
            return;
        }

        if (! app(ExamWorkflowService::class)->activeExamStatus($value)) {
            $fail(tkey('exams.validation.active_exam_status'));
        }
    }
}
