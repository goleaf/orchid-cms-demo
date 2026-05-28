<?php

namespace App\Rules;

use App\Models\ExamSession;
use App\Services\Exams\ExamWorkflowService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ExamSessionCapacityRule implements ValidationRule
{
    public function __construct(
        private readonly ?ExamSession $session = null,
        private readonly bool $allowOverbooking = false,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($this->allowOverbooking || ! filled($value)) {
            return;
        }

        $service = app(ExamWorkflowService::class);
        $session = $this->session;

        if ($session !== null && is_numeric($value)) {
            if (! $service->sessionHasCapacity($session, (int) $value)) {
                $fail(tkey('exams.validation.session_capacity_unavailable'));
            }

            return;
        }

        $session ??= $value instanceof ExamSession ? $value : ExamSession::query()->find($value);

        if ($session === null || ! $service->sessionHasCapacity($session)) {
            $fail(tkey('exams.validation.session_capacity_unavailable'));
        }
    }
}
