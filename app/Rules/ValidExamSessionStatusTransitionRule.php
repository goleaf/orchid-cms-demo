<?php

namespace App\Rules;

use App\Models\ExamSession;
use App\Services\Exams\ExamWorkflowService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Throwable;

class ValidExamSessionStatusTransitionRule implements ValidationRule
{
    public function __construct(
        private readonly ?ExamSession $session = null,
        private readonly bool $allowOverride = false,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($this->session === null || ! filled($value)) {
            return;
        }

        try {
            if (app(ExamWorkflowService::class)->canTransitionSessionStatus($this->session, $value, $this->allowOverride)) {
                return;
            }
        } catch (Throwable) {
        }

        $fail(tkey('exams.validation.invalid_session_status_transition'));
    }
}
