<?php

namespace App\Rules;

use App\Models\NotificationTemplate;
use App\Models\NotificationTemplateVersion;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PublishedNotificationTemplateRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! filled($value)) {
            return;
        }

        $exists = NotificationTemplate::query()
            ->active()
            ->whereKey($value)
            ->whereHas('versions', fn ($query) => $query->where('status', NotificationTemplateVersion::STATUS_PUBLISHED))
            ->exists();

        if ($exists) {
            return;
        }

        $fail(tkey('notifications.validation.template_not_published'));
    }
}
