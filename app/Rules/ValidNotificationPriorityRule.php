<?php

namespace App\Rules;

use App\Models\NotificationMessage;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidNotificationPriorityRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (filled($value) && in_array((string) $value, NotificationMessage::priorityValues(), true)) {
            return;
        }

        $fail(tkey('notifications.validation.invalid_priority'));
    }
}
