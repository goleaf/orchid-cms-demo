<?php

namespace App\Rules;

use App\Models\ExamAttempt;
use App\Services\Exams\ExamWorkflowService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ExamAttemptCanCompleteRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! filled($value)) {
            return;
        }

        $attempt = $value instanceof ExamAttempt ? $value : ExamAttempt::query()->find($value);

        if ($attempt === null || ! app(ExamWorkflowService::class)->attemptCanComplete($attempt)) {
            $fail(tkey('exams.validation.attempt_cannot_complete'));
        }
    }
}
