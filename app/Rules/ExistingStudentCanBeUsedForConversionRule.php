<?php

namespace App\Rules;

use App\Models\Student;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ExistingStudentCanBeUsedForConversionRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! filled($value)) {
            return;
        }

        $student = Student::query()
            ->whereKey($value)
            ->first();

        if ($student === null || $student->is_archived) {
            $fail(tkey('students.conversion.validation.existing_student_invalid'));
        }
    }
}
