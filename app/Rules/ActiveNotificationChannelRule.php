<?php

namespace App\Rules;

use App\Models\NotificationChannel;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ActiveNotificationChannelRule implements ValidationRule
{
    public function __construct(
        private readonly bool $mustSupportTemplates = false,
        private readonly bool $mustSupportScheduling = false,
        private readonly ?string $messageKey = null,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! filled($value)) {
            return;
        }

        $query = NotificationChannel::query()
            ->active()
            ->whereKey($value);

        if ($this->mustSupportTemplates) {
            $query->where('supports_templates', true);
        }

        if ($this->mustSupportScheduling) {
            $query->where('supports_scheduling', true);
        }

        if ($query->exists()) {
            return;
        }

        $fail(tkey($this->messageKey ?? 'communication.validation.channel_unavailable'));
    }
}
