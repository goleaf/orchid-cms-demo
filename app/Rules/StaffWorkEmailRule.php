<?php

namespace App\Rules;

use App\Models\StaffProfile;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class StaffWorkEmailRule implements ValidationRule
{
    public function __construct(private readonly StaffProfile|int|null $ignore = null) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (blank($value)) {
            return;
        }

        if (filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
            $fail(tkey('security.validation.invalid_staff_work_email'));

            return;
        }

        $ignoreId = $this->ignore instanceof StaffProfile ? $this->ignore->getKey() : $this->ignore;
        $exists = StaffProfile::query()
            ->where('work_email', (string) $value)
            ->when($ignoreId !== null, fn ($query) => $query->whereKeyNot((int) $ignoreId))
            ->exists();

        if ($exists) {
            $fail(tkey('security.validation.invalid_staff_work_email'));
        }
    }
}
