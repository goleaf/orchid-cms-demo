<?php

namespace App\Rules;

use App\Models\Student;
use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class StudentCanBeArchivedRule implements ValidationRule
{
    public function __construct(
        private readonly ?Student $student,
        private readonly ?User $user = null,
        private readonly bool $allowOverride = false,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($this->student === null) {
            return;
        }

        if ($this->allowOverride || ($this->user?->hasAccess('students.archive_with_active_enrollment') ?? false)) {
            return;
        }

        if ($this->student->activeEnrollments()->exists()) {
            $fail(tkey('students.validation.student_cannot_be_archived'));
        }
    }
}
