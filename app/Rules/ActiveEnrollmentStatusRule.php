<?php

namespace App\Rules;

use App\Models\EnrollmentStatus;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ActiveEnrollmentStatusRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (
            filled($value)
            && EnrollmentStatus::query()
                ->active()
                ->where('code', (string) $value)
                ->exists()
        ) {
            return;
        }

        $fail(tkey('students.validation.enrollment_status_not_active'));
    }
}
