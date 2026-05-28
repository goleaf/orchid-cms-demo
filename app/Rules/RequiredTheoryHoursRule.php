<?php

namespace App\Rules;

use App\Models\StudentEnrollment;
use App\Services\Exams\ExamWorkflowService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class RequiredTheoryHoursRule implements ValidationRule
{
    public function __construct(private readonly mixed $requiredHours = null) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! filled($value)) {
            return;
        }

        $enrollment = $value instanceof StudentEnrollment ? $value : StudentEnrollment::query()->find($value);

        if ($enrollment === null || ! app(ExamWorkflowService::class)->theoryHoursMet($enrollment, $this->requiredHours)) {
            $fail(tkey('exams.validation.theory_hours_required'));
        }
    }
}
