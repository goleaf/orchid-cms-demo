<?php

namespace App\Rules;

use App\Models\StudentEnrollment;
use App\Models\TrainingGroup;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class StudentEnrollmentCanJoinGroupRule implements ValidationRule
{
    public function __construct(private readonly ?TrainingGroup $group = null) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $enrollment = filled($value) ? StudentEnrollment::query()->find($value) : null;

        if ($enrollment === null || $this->group === null) {
            $fail(tkey('education.groups.validation.enrollment_cannot_join_group'));

            return;
        }

        if (
            $enrollment->training_program_id !== null
            && $this->group->training_program_id !== null
            && (int) $enrollment->training_program_id !== (int) $this->group->training_program_id
        ) {
            $fail(tkey('education.groups.validation.enrollment_cannot_join_group'));
        }
    }
}
