<?php

namespace App\Rules;

use App\Models\NotificationMessage;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NotificationCanBeSentRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! filled($value)) {
            return;
        }

        $message = $value instanceof NotificationMessage
            ? $value
            : NotificationMessage::query()->withCount('recipients')->find($value);

        if ($message !== null
            && in_array($message->status, [NotificationMessage::STATUS_DRAFT, NotificationMessage::STATUS_SCHEDULED, NotificationMessage::STATUS_QUEUED], true)
            && ($message->recipients_count ?? $message->recipients()->count()) > 0) {
            return;
        }

        $fail(tkey('notifications.validation.message_cannot_be_sent'));
    }
}
