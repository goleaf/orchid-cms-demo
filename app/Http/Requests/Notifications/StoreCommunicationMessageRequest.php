<?php

namespace App\Http\Requests\Notifications;

use App\Models\CommunicationThread;
use App\Models\NotificationChannel;
use App\Rules\ActiveNotificationChannelRule;
use App\Rules\SafeNotificationTemplateContentRule;
use App\Rules\ValidCommunicationDirectionRule;
use App\Rules\ValidNotificationTargetRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCommunicationMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyAccess(['communications.student_history.manage', 'communications.lead_history.manage']) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'thread.id' => ['nullable', 'integer', Rule::exists(CommunicationThread::class, 'id')],
            'thread.subject' => ['nullable', 'string', 'max:255'],
            'thread.target_type' => ['required_without:thread.id', 'string', 'max:120'],
            'thread.target_id' => ['required_without:thread.id', 'integer', new ValidNotificationTargetRule($this->input('thread.target_type'))],
            'message.direction' => ['required', 'string', new ValidCommunicationDirectionRule],
            'message.channel_id' => ['required', 'integer', Rule::exists(NotificationChannel::class, 'id'), new ActiveNotificationChannelRule(messageKey: 'notifications.validation.channel_unavailable')],
            'message.body' => ['required', 'string', 'max:10000', new SafeNotificationTemplateContentRule],
            'message.sent_at' => ['nullable', 'date'],
            'attachments' => ['nullable', 'array'],
            'attachments.*.path' => ['required_with:attachments', 'string', 'max:500'],
            'attachments.*.disk' => ['nullable', 'string', 'max:80'],
            'attachments.*.original_name' => ['nullable', 'string', 'max:255'],
            'attachments.*.mime_type' => ['nullable', 'string', 'max:120'],
            'attachments.*.size' => ['nullable', 'integer', 'min:0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'thread.target_type.required_without' => tkey('notifications.validation.target_type_required'),
            'thread.target_id.required_without' => tkey('notifications.validation.target_required'),
            'message.direction.required' => tkey('notifications.validation.direction_required'),
            'message.channel_id.required' => tkey('notifications.validation.channel_required'),
            'message.body.required' => tkey('notifications.validation.communication_body_required'),
            'attachments.*.path.required_with' => tkey('notifications.validation.attachment_path_required'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function threadData(): array
    {
        return $this->validated('thread', []);
    }

    /**
     * @return array<string, mixed>
     */
    public function messageData(): array
    {
        return [
            ...$this->validated('message'),
            'user_id' => $this->user()?->id,
            'attachments' => $this->validated('attachments', []),
        ];
    }
}
