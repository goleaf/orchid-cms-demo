<?php

namespace App\Rules;

use App\Models\ExamAdmission;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ExamAdmissionReadyRule implements ValidationRule
{
    public function __construct(private readonly bool $allowOverride = false) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! filled($value) || $this->allowOverride) {
            return;
        }

        $admission = $value instanceof ExamAdmission
            ? $value
            : ExamAdmission::query()->with('checklistItems')->find($value);

        if ($admission === null || ! $admission->isReady()) {
            $fail(tkey('exams.validation.admission_not_ready'));
        }
    }
}
