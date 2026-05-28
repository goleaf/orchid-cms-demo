<?php

namespace App\Rules;

use App\Models\NotificationChannel;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NotificationPreferenceAllowedRule implements ValidationRule
{
    /**
     * @param  array<string, mixed>|null  $preference
     */
    public function __construct(private readonly ?array $preference = null) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $preference = is_array($value) ? $value : ($this->preference ?? []);
        $targetCount = collect(['user_id', 'student_id', 'lead_id'])
            ->filter(fn (string $field): bool => filled($preference[$field] ?? null))
            ->count();

        if ($targetCount !== 1) {
            $fail(tkey('notifications.validation.preference_target_required'));

            return;
        }

        $channelId = $preference['channel_id'] ?? null;

        if (! filled($channelId) || ! NotificationChannel::query()->active()->whereKey($channelId)->exists()) {
            $fail(tkey('notifications.validation.preference_channel_unavailable'));
        }
    }
}
