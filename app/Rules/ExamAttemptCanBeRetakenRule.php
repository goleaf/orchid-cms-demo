<?php

namespace App\Rules;

use App\Enums\ExamAttemptStatus;
use App\Models\ExamAttempt;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ExamAttemptCanBeRetakenRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! filled($value)) {
            return;
        }

        $attempt = $value instanceof ExamAttempt
            ? $value
            : ExamAttempt::query()->find($value);

        if ($attempt === null || ! $attempt->status->canBeRetaken()) {
            $fail(tkey('exams.validation.attempt_cannot_be_retaken'));

            return;
        }

        $hasOpenRetake = $attempt->retakes()
            ->whereIn('status', [
                ExamAttemptStatus::Scheduled->value,
                ExamAttemptStatus::InProgress->value,
            ])
            ->exists();

        if ($hasOpenRetake) {
            $fail(tkey('exams.validation.attempt_already_has_open_retake'));
        }
    }
}
