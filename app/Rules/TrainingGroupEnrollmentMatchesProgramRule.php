<?php

namespace App\Rules;

use App\Models\StudentEnrollment;
use App\Models\TrainingGroup;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class TrainingGroupEnrollmentMatchesProgramRule implements ValidationRule
{
    public function __construct(private readonly ?StudentEnrollment $enrollment) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! filled($value) || $this->enrollment === null) {
            return;
        }

        $group = TrainingGroup::query()->find($value);

        if (
            $group !== null
            && $this->enrollment->training_program_id !== null
            && $group->training_program_id !== null
            && (int) $this->enrollment->training_program_id !== (int) $group->training_program_id
        ) {
            $fail(tkey('education.validation.enrollment_program_mismatch'));
        }
    }
}
