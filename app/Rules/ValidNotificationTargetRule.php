<?php

namespace App\Rules;

use App\Support\Notifications\NotificationTargetResolver;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidNotificationTargetRule implements ValidationRule
{
    public function __construct(
        private readonly ?string $targetType = null,
        private readonly mixed $targetId = null,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $targetType = $this->targetType;
        $targetId = $this->targetId;

        if (is_array($value)) {
            $targetType = $targetType ?? (is_string($value['target_type'] ?? null) ? $value['target_type'] : null);
            $targetId = $targetId ?? ($value['target_id'] ?? null);
        } elseif ($targetId === null) {
            $targetId = $value;
        }

        if (app(NotificationTargetResolver::class)->resolve($targetType, $targetId) !== null) {
            return;
        }

        $fail(tkey('notifications.validation.invalid_target'));
    }
}
