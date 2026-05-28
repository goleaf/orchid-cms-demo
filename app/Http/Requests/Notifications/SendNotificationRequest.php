<?php

namespace App\Http\Requests\Notifications;

use App\Models\NotificationMessage;
use App\Rules\NotificationCanBeSentRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SendNotificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyAccess(['communications.delivery_logs.view', 'communications.templates.manage']) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'message_id' => ['required', 'integer', Rule::exists(NotificationMessage::class, 'id'), new NotificationCanBeSentRule],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'message_id.required' => tkey('notifications.validation.message_required'),
            'message_id.exists' => tkey('notifications.validation.message_unavailable'),
        ];
    }
}
