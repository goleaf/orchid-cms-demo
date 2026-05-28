<?php

namespace App\Rules;

use App\Actions\ChangeEnrollmentStatusAction;
use App\Enums\EnrollmentStatus;
use App\Models\StudentEnrollment;
use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use ValueError;

class ValidEnrollmentStatusTransitionRule implements ValidationRule
{
    public function __construct(
        private readonly ?StudentEnrollment $enrollment,
        private readonly ?User $user = null,
        private readonly bool $allowOverride = false,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($this->enrollment === null || ! filled($value)) {
            return;
        }

        try {
            $target = $value instanceof EnrollmentStatus ? $value : EnrollmentStatus::from((string) $value);
        } catch (ValueError) {
            $fail(tkey('students.validation.invalid_enrollment_status_transition'));

            return;
        }

        if ($this->allowOverride || ($this->user?->hasAccess('students.enrollments.override_status_transition') ?? false)) {
            return;
        }

        if (! app(ChangeEnrollmentStatusAction::class)->transitionAllowed($this->enrollment->status, $target)) {
            $fail(tkey('students.validation.invalid_enrollment_status_transition'));
        }
    }
}
