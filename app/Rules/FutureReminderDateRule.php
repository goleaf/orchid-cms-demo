<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Carbon;

class FutureReminderDateRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! filled($value)) {
            return;
        }

        try {
            $date = Carbon::parse((string) $value);
        } catch (\Throwable) {
            $fail(tkey('communication.validation.reminder_due_at_invalid'));

            return;
        }

        if ($date->isFuture()) {
            return;
        }

        $fail(tkey('communication.validation.reminder_due_at_future'));
    }
}
