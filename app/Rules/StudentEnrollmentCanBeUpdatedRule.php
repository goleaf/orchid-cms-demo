<?php

namespace App\Rules;

use App\Models\StudentEnrollment;
use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class StudentEnrollmentCanBeUpdatedRule implements ValidationRule
{
    public function __construct(
        private readonly ?StudentEnrollment $enrollment,
        private readonly ?User $user = null,
        private readonly bool $allowOverride = false,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($this->enrollment === null) {
            return;
        }

        if ($this->allowOverride || ($this->user?->hasAccess('students.enrollments.update_locked') ?? false)) {
            return;
        }

        if ($this->enrollment->is_completed) {
            $fail(tkey('students.validation.completed_enrollment_locked'));

            return;
        }

        if ($this->enrollment->is_cancelled) {
            $fail(tkey('students.validation.cancelled_enrollment_locked'));
        }
    }
}
