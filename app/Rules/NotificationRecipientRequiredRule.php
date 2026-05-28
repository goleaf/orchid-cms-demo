<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NotificationRecipientRequiredRule implements ValidationRule
{
    /**
     * @param  array<string, mixed>|null  $recipient
     */
    public function __construct(private readonly ?array $recipient = null) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $recipient = is_array($value) ? $value : ($this->recipient ?? []);

        foreach (['user_id', 'student_id', 'lead_id', 'email', 'phone'] as $field) {
            if (filled($recipient[$field] ?? null)) {
                return;
            }
        }

        $fail(tkey('notifications.validation.recipient_required'));
    }
}
