<?php

namespace App\Http\Requests\Communication;

use App\Models\NotificationChannel;
use App\Models\User;
use App\Rules\ActiveNotificationChannelRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserNotificationPreferenceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAccess('communications.preferences.manage') ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'preference.user_id' => ['required', 'integer', Rule::exists(User::class, 'id')],
            'preference.notification_channel_id' => ['required', 'integer', Rule::exists(NotificationChannel::class, 'id'), new ActiveNotificationChannelRule],
            'preference.event' => ['required', 'string', 'max:120'],
            'preference.is_enabled' => ['nullable', 'boolean'],
            'preference.quiet_hours_start' => ['nullable', 'date_format:H:i'],
            'preference.quiet_hours_end' => ['nullable', 'date_format:H:i'],
            'preference.send_reminder_before_minutes' => ['nullable', 'integer', 'min:0', 'max:10080'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function preferenceData(): array
    {
        $data = $this->validated('preference');
        $data['is_enabled'] = (bool) ($data['is_enabled'] ?? false);

        return $data;
    }
}
