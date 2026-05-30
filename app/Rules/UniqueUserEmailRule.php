<?php

namespace App\Rules;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UniqueUserEmailRule implements ValidationRule
{
    public function __construct(private readonly User|int|null $ignore = null) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (blank($value)) {
            return;
        }

        $ignoreId = $this->ignore instanceof User ? $this->ignore->getKey() : $this->ignore;
        $exists = User::query()
            ->where('email', (string) $value)
            ->when($ignoreId !== null, fn ($query) => $query->whereKeyNot((int) $ignoreId))
            ->exists();

        if ($exists) {
            $fail(tkey('security.validation.user_email_not_unique'));
        }
    }
}
