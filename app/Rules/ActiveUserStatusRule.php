<?php

namespace App\Rules;

use App\Models\UserStatus;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ActiveUserStatusRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (blank($value)) {
            return;
        }

        $status = $this->status($value);

        if ($status?->is_active) {
            return;
        }

        $fail(tkey('security.validation.user_status_not_active'));
    }

    private function status(mixed $value): ?UserStatus
    {
        if ($value instanceof UserStatus) {
            return $value;
        }

        return UserStatus::query()
            ->when(is_numeric($value), fn ($query) => $query->whereKey((int) $value))
            ->when(! is_numeric($value), fn ($query) => $query->where('code', (string) $value))
            ->first();
    }
}
