<?php

namespace App\Rules;

use App\Models\StaffProfile;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class StaffProfileUserUniqueRule implements ValidationRule
{
    public function __construct(
        private readonly ?int $ignoreProfileId = null,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (blank($value)) {
            return;
        }

        $exists = StaffProfile::query()
            ->where('user_id', (int) $value)
            ->when($this->ignoreProfileId !== null, fn ($query) => $query->whereKeyNot($this->ignoreProfileId))
            ->exists();

        if ($exists) {
            $fail(tkey('security.validation.staff_profile_user_unique'));
        }
    }
}
