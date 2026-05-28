<?php

namespace App\Http\Requests\Notifications;

use App\Models\NotificationMessage;
use App\Models\ReminderRule;
use App\Rules\ReminderScheduleDateRule;
use App\Rules\ValidNotificationTargetRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ScheduleReminderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAccess('communications.reminders.manage') ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'schedule.rule_id' => ['required', 'integer', Rule::exists(ReminderRule::class, 'id')],
            'schedule.target_type' => ['required', 'string', 'max:120'],
            'schedule.target_id' => ['required', 'integer', new ValidNotificationTargetRule($this->input('schedule.target_type'))],
            'schedule.message_id' => ['nullable', 'integer', Rule::exists(NotificationMessage::class, 'id')],
            'schedule.scheduled_at' => ['required', 'date', new ReminderScheduleDateRule],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'schedule.rule_id.required' => tkey('notifications.validation.reminder_rule_required'),
            'schedule.target_type.required' => tkey('notifications.validation.target_type_required'),
            'schedule.target_id.required' => tkey('notifications.validation.target_required'),
            'schedule.scheduled_at.required' => tkey('notifications.validation.schedule_date_required'),
            'schedule.scheduled_at.date' => tkey('notifications.validation.invalid_schedule_date'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function scheduleData(): array
    {
        return $this->validated('schedule');
    }
}
