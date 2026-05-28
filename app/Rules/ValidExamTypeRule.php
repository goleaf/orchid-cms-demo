<?php

namespace App\Rules;

use App\Enums\ExamType;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidExamTypeRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! filled($value)) {
            return;
        }

        $valid = collect(ExamType::cases())
            ->contains(fn (ExamType $type): bool => $type->value === (string) $value);

        if (! $valid) {
            $fail(tkey('exams.validation.invalid_exam_type'));
        }
    }
}
