<?php

namespace App\Http\Requests\Notifications;

use App\Models\Lead;
use App\Models\NotificationChannel;
use App\Models\StudentProfile;
use App\Models\User;
use App\Rules\ActiveNotificationChannelRule;
use App\Rules\NotificationPreferenceAllowedRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateNotificationPreferenceRequest extends FormRequest
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
            'preference' => ['required', 'array', new NotificationPreferenceAllowedRule],
            'preference.user_id' => ['nullable', 'integer', Rule::exists(User::class, 'id')],
            'preference.student_id' => ['nullable', 'integer', Rule::exists(StudentProfile::class, 'id')],
            'preference.lead_id' => ['nullable', 'integer', Rule::exists(Lead::class, 'id')],
            'preference.channel_id' => ['required', 'integer', Rule::exists(NotificationChannel::class, 'id'), new ActiveNotificationChannelRule(messageKey: 'notifications.validation.channel_unavailable')],
            'preference.enabled' => ['nullable', 'boolean'],
            'preference.locale' => ['nullable', 'string', 'max:12'],
            'preference.settings' => ['nullable', 'array'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'preference.required' => tkey('notifications.validation.preference_required'),
            'preference.channel_id.required' => tkey('notifications.validation.channel_required'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function preferenceData(): array
    {
        $data = $this->validated('preference');
        $data['enabled'] = (bool) ($data['enabled'] ?? true);

        return $data;
    }
}
