<?php

namespace App\Rules;

use App\Models\ExamAdmissionRule;
use App\Models\StudentEnrollment;
use App\Services\Exams\ExamWorkflowService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class RequiredPaymentsCompletedRule implements ValidationRule
{
    public function __construct(private readonly ?ExamAdmissionRule $rule = null) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! filled($value)) {
            return;
        }

        $enrollment = $value instanceof StudentEnrollment ? $value : StudentEnrollment::query()->find($value);

        if ($enrollment === null || ! app(ExamWorkflowService::class)->paymentsCompleted($enrollment, $this->rule)) {
            $fail(tkey('exams.validation.required_payments_completed'));
        }
    }
}
