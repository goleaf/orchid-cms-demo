<?php

namespace App\Rules;

use App\Models\StudentStatus;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ActiveStudentStatusRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (
            filled($value)
            && StudentStatus::query()
                ->active()
                ->where('code', (string) $value)
                ->exists()
        ) {
            return;
        }

        $fail(tkey('students.validation.status_not_active'));
    }
}
