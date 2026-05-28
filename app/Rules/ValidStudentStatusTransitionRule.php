<?php

namespace App\Rules;

use App\Actions\ChangeStudentStatusAction;
use App\Enums\StudentStatus;
use App\Models\Student;
use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use ValueError;

class ValidStudentStatusTransitionRule implements ValidationRule
{
    public function __construct(
        private readonly ?Student $student,
        private readonly ?User $user = null,
        private readonly bool $allowOverride = false,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($this->student === null || ! filled($value)) {
            return;
        }

        try {
            $target = $value instanceof StudentStatus ? $value : StudentStatus::from((string) $value);
        } catch (ValueError) {
            $fail(tkey('students.validation.invalid_student_status_transition'));

            return;
        }

        if ($this->allowOverride || ($this->user?->hasAccess('students.override_status_transition') ?? false)) {
            return;
        }

        if (! app(ChangeStudentStatusAction::class)->transitionAllowed($this->student->status, $target)) {
            $fail(tkey('students.validation.invalid_student_status_transition'));
        }
    }
}
