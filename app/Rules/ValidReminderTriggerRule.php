<?php

namespace App\Rules;

use App\Models\ReminderRule;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidReminderTriggerRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (filled($value) && in_array((string) $value, ReminderRule::triggerValues(), true)) {
            return;
        }

        $fail(tkey('notifications.validation.invalid_reminder_trigger'));
    }
}
