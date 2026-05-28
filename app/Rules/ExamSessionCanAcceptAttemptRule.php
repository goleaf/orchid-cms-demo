<?php

namespace App\Rules;

use App\Models\ExamSession;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ExamSessionCanAcceptAttemptRule implements ValidationRule
{
    public function __construct(private readonly bool $allowFullSession = false) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! filled($value) || $this->allowFullSession) {
            return;
        }

        $session = $value instanceof ExamSession
            ? $value
            : ExamSession::query()->find($value);

        if ($session === null || ! $session->acceptsAttempt()) {
            $fail(tkey('exams.validation.session_full_or_closed'));
        }
    }
}
