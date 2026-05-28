<?php

namespace App\Rules;

use App\Models\NotificationDelivery;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NotificationCanBeRetriedRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! filled($value)) {
            return;
        }

        $delivery = $value instanceof NotificationDelivery
            ? $value
            : NotificationDelivery::query()->find($value);

        if ($delivery?->status === NotificationDelivery::STATUS_FAILED) {
            return;
        }

        $fail(tkey('notifications.validation.delivery_cannot_be_retried'));
    }
}
