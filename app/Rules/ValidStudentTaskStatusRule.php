<?php

namespace App\Rules;

use App\Enums\StudentTaskStatus;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidStudentTaskStatusRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (filled($value) && in_array((string) $value, StudentTaskStatus::values(), true)) {
            return;
        }

        $fail(tkey('students.validation.invalid_task_status'));
    }
}
