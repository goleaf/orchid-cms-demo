<?php

namespace App\Rules;

use App\Enums\GearboxType;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidGearboxTypeRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! filled($value) || in_array((string) $value, GearboxType::values(), true)) {
            return;
        }

        $fail(tkey('students.validation.invalid_gearbox_type'));
    }
}
