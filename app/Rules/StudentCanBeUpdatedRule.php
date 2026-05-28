<?php

namespace App\Rules;

use App\Models\Student;
use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class StudentCanBeUpdatedRule implements ValidationRule
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

        if ($this->allowOverride || ($this->user?->hasAccess('students.update_archived') ?? false)) {
            return;
        }

        if ($this->student->is_archived) {
            $fail(tkey('students.validation.archived_student_locked'));

            return;
        }

        if ($this->student->is_blocked) {
            $fail(tkey('students.validation.student_cannot_be_updated'));
        }
    }
}
