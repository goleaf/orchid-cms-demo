<?php

namespace App\Http\Requests\Communication;

use App\Models\CommunicationTemplate;
use App\Models\NotificationChannel;
use App\Models\StudentEnrollment;
use App\Models\StudentProfile;
use App\Rules\ActiveCommunicationTemplateForChannelRule;
use App\Rules\ActiveNotificationChannelRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StudentCommunicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAccess('communications.student_history.manage') ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'communication.student_profile_id' => ['required', 'integer', Rule::exists(StudentProfile::class, 'id')],
            'communication.student_enrollment_id' => ['nullable', 'integer', Rule::exists(StudentEnrollment::class, 'id')],
            'communication.notification_channel_id' => ['required', 'integer', Rule::exists(NotificationChannel::class, 'id'), new ActiveNotificationChannelRule],
            'communication.communication_template_id' => ['nullable', 'integer', Rule::exists(CommunicationTemplate::class, 'id'), new ActiveCommunicationTemplateForChannelRule($this->input('communication.notification_channel_id'))],
            'communication.direction' => ['required', Rule::in(['inbound', 'outbound'])],
            'communication.subject' => ['nullable', 'string', 'max:255'],
            'communication.body' => ['nullable', 'required_without:communication.communication_template_id', 'string', 'max:5000'],
            'communication.client_replied_at' => ['nullable', 'date'],
            'communication.callback_required_at' => ['nullable', 'date'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'communication.student_profile_id.required' => tkey('communication.validation.student_required'),
            'communication.notification_channel_id.required' => tkey('communication.validation.channel_required'),
            'communication.direction.required' => tkey('communication.validation.direction_required'),
            'communication.body.required_without' => tkey('communication.validation.communication_body_required'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function communicationData(): array
    {
        return $this->validated('communication');
    }
}
