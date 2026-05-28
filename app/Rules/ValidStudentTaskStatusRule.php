<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidStudentTaskStatusRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (filled($value) && in_array((string) $value, ['open', 'in_progress', 'done', 'cancelled'], true)) {
            return;
        }

        $fail(tkey('students.validation.invalid_task_status'));
    }
}
