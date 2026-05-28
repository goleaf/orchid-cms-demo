<?php

namespace App\Http\Requests\Notifications;

use App\Models\Lead;
use App\Models\NotificationChannel;
use App\Models\NotificationMessage;
use App\Models\NotificationTemplate;
use App\Models\NotificationTemplateVersion;
use App\Models\StudentProfile;
use App\Models\User;
use App\Rules\ActiveNotificationChannelRule;
use App\Rules\NotificationRecipientRequiredRule;
use App\Rules\PublishedNotificationTemplateRule;
use App\Rules\SafeNotificationTemplateContentRule;
use App\Rules\ValidNotificationPriorityRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreNotificationMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyAccess(['communications.templates.manage', 'communications.delivery_logs.view']) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'message.channel_id' => ['required', 'integer', Rule::exists(NotificationChannel::class, 'id'), new ActiveNotificationChannelRule(messageKey: 'notifications.validation.channel_unavailable')],
            'message.template_id' => ['nullable', 'integer', Rule::exists(NotificationTemplate::class, 'id'), new PublishedNotificationTemplateRule],
            'message.template_version_id' => ['nullable', 'integer', Rule::exists(NotificationTemplateVersion::class, 'id')],
            'message.subject' => ['nullable', 'string', 'max:255', new SafeNotificationTemplateContentRule],
            'message.body' => ['nullable', 'required_without:message.template_id', 'string', 'max:10000', new SafeNotificationTemplateContentRule],
            'message.priority' => ['required', 'string', new ValidNotificationPriorityRule],
            'message.status' => ['nullable', Rule::in(NotificationMessage::statusValues())],
            'message.scheduled_at' => ['nullable', 'date'],
            'recipients' => ['required', 'array', 'min:1'],
            'recipients.*' => ['required', 'array', new NotificationRecipientRequiredRule],
            'recipients.*.user_id' => ['nullable', 'integer', Rule::exists(User::class, 'id')],
            'recipients.*.student_id' => ['nullable', 'integer', Rule::exists(StudentProfile::class, 'id')],
            'recipients.*.lead_id' => ['nullable', 'integer', Rule::exists(Lead::class, 'id')],
            'recipients.*.email' => ['nullable', 'email:rfc', 'max:190'],
            'recipients.*.phone' => ['nullable', 'string', 'max:80'],
            'recipients.*.locale' => ['nullable', 'string', 'max:12'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'message.channel_id.required' => tkey('notifications.validation.channel_required'),
            'message.body.required_without' => tkey('notifications.validation.message_body_required'),
            'message.priority.required' => tkey('notifications.validation.priority_required'),
            'recipients.required' => tkey('notifications.validation.recipient_required'),
            'recipients.min' => tkey('notifications.validation.recipient_required'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function messageData(): array
    {
        return [
            ...$this->validated('message'),
            'recipients' => $this->validated('recipients', []),
            'created_by_id' => $this->user()?->id,
        ];
    }
}
