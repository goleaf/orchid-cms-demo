<?php

namespace App\Http\Requests\Communication;

use App\Models\CommunicationReminder;
use App\Models\CommunicationTemplate;
use App\Models\MarketingLead;
use App\Models\NotificationChannel;
use App\Models\StudentEnrollment;
use App\Models\StudentProfile;
use App\Models\User;
use App\Rules\ActiveCommunicationTemplateForChannelRule;
use App\Rules\ActiveNotificationChannelRule;
use App\Rules\FutureReminderDateRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CommunicationReminderRequest extends FormRequest
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
            'reminder.id' => ['nullable', 'integer', Rule::exists(CommunicationReminder::class, 'id')],
            'reminder.marketing_lead_id' => ['nullable', 'integer', Rule::exists(MarketingLead::class, 'id')],
            'reminder.student_profile_id' => ['nullable', 'integer', Rule::exists(StudentProfile::class, 'id')],
            'reminder.student_enrollment_id' => ['nullable', 'integer', Rule::exists(StudentEnrollment::class, 'id')],
            'reminder.assigned_to_user_id' => ['nullable', 'integer', Rule::exists(User::class, 'id')],
            'reminder.notification_channel_id' => ['nullable', 'integer', Rule::exists(NotificationChannel::class, 'id'), new ActiveNotificationChannelRule(mustSupportScheduling: true)],
            'reminder.communication_template_id' => ['nullable', 'integer', Rule::exists(CommunicationTemplate::class, 'id'), new ActiveCommunicationTemplateForChannelRule($this->input('reminder.notification_channel_id'))],
            'reminder.status' => ['required', 'string', Rule::in(CommunicationReminder::statusValues())],
            'reminder.priority' => ['required', 'string', Rule::in(CommunicationReminder::priorityValues())],
            'reminder.title_translations' => ['nullable', 'array'],
            'reminder.title_translations.*' => ['nullable', 'string', 'max:255'],
            'reminder.body_translations' => ['nullable', 'array'],
            'reminder.body_translations.*' => ['nullable', 'string', 'max:2000'],
            'reminder.note' => ['nullable', 'string', 'max:2000'],
            'reminder.due_at' => ['required', 'date', new FutureReminderDateRule],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'reminder.status.required' => tkey('communication.validation.reminder_status_required'),
            'reminder.status.in' => tkey('communication.validation.reminder_status_invalid'),
            'reminder.priority.required' => tkey('communication.validation.reminder_priority_required'),
            'reminder.priority.in' => tkey('communication.validation.reminder_priority_invalid'),
            'reminder.due_at.required' => tkey('communication.validation.reminder_due_at_required'),
            'reminder.due_at.date' => tkey('communication.validation.reminder_due_at_invalid'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function reminderData(): array
    {
        $data = $this->validated('reminder');
        unset($data['id']);

        foreach (['marketing_lead_id', 'student_profile_id', 'student_enrollment_id', 'assigned_to_user_id', 'notification_channel_id', 'communication_template_id'] as $field) {
            $data[$field] = filled($data[$field] ?? null) ? (int) $data[$field] : null;
        }

        return $data;
    }

    public function reminderId(): ?int
    {
        $id = $this->validated('reminder.id', null);

        return filled($id) ? (int) $id : null;
    }
}
