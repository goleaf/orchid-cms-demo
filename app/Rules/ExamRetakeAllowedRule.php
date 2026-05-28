<?php

namespace App\Rules;

use App\Models\ExamAttempt;
use App\Services\Exams\ExamWorkflowService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ExamRetakeAllowedRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! filled($value)) {
            return;
        }

        $attempt = $value instanceof ExamAttempt ? $value : ExamAttempt::query()->find($value);

        if ($attempt === null || ! app(ExamWorkflowService::class)->retakeAllowed($attempt)) {
            $fail(tkey('exams.validation.retake_not_allowed'));
        }
    }
}
