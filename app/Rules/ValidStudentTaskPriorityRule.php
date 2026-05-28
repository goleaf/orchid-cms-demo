<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidStudentTaskPriorityRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (filled($value) && in_array((string) $value, ['low', 'normal', 'high', 'urgent'], true)) {
            return;
        }

        $fail(tkey('students.validation.invalid_task_priority'));
    }
}
