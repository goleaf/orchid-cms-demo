<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Carbon;

class ReminderScheduleDateRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! filled($value)) {
            return;
        }

        try {
            $scheduledAt = Carbon::parse((string) $value);
        } catch (\Throwable) {
            $fail(tkey('notifications.validation.invalid_schedule_date'));

            return;
        }

        if ($scheduledAt->greaterThan(now())) {
            return;
        }

        $fail(tkey('notifications.validation.schedule_date_must_be_future'));
    }
}
