<?php

namespace App\Http\Requests\Notifications;

use App\Models\NotificationChannel;
use App\Models\NotificationTemplate;
use App\Models\NotificationTemplateVersion;
use App\Rules\ActiveNotificationChannelRule;
use App\Rules\SafeNotificationTemplateContentRule;
use App\Rules\TranslatedCommunicationFieldRequiredRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreNotificationTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyAccess(['communications.templates.manage', 'communications.channels.manage']) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $templateId = $this->integer('template.id') ?: null;

        return [
            'template.id' => ['nullable', 'integer', Rule::exists(NotificationTemplate::class, 'id')],
            'template.code' => ['required', 'string', 'max:120', Rule::unique(NotificationTemplate::class, 'code')->ignore($templateId)],
            'template.channel_id' => ['nullable', 'integer', Rule::exists(NotificationChannel::class, 'id'), new ActiveNotificationChannelRule(mustSupportTemplates: true, messageKey: 'notifications.validation.channel_unavailable')],
            'template.name_translations' => ['required', 'array', new TranslatedCommunicationFieldRequiredRule('notifications.validation.default_translation_required')],
            'template.name_translations.*' => ['nullable', 'string', 'max:255'],
            'template.description_translations' => ['nullable', 'array'],
            'template.description_translations.*' => ['nullable', 'string', 'max:1000'],
            'template.template_group' => ['required', 'string', 'max:120'],
            'template.is_active' => ['nullable', 'boolean'],
            'template.is_system' => ['nullable', 'boolean'],
            'version.status' => ['required', Rule::in([NotificationTemplateVersion::STATUS_DRAFT, NotificationTemplateVersion::STATUS_PUBLISHED, NotificationTemplateVersion::STATUS_ARCHIVED])],
            'version.subject_translations' => ['nullable', 'array', new SafeNotificationTemplateContentRule],
            'version.subject_translations.*' => ['nullable', 'string', 'max:255'],
            'version.body_translations' => ['required', 'array', new TranslatedCommunicationFieldRequiredRule('notifications.validation.default_translation_required'), new SafeNotificationTemplateContentRule],
            'version.body_translations.*' => ['nullable', 'string', 'max:5000'],
            'version.variables_schema' => ['nullable', 'array'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'template.code.required' => tkey('notifications.validation.template_code_required'),
            'template.code.unique' => tkey('notifications.validation.template_code_unique'),
            'template.name_translations.required' => tkey('notifications.validation.default_translation_required'),
            'template.template_group.required' => tkey('notifications.validation.template_group_required'),
            'version.status.required' => tkey('notifications.validation.template_status_required'),
            'version.status.in' => tkey('notifications.validation.template_status_invalid'),
            'version.body_translations.required' => tkey('notifications.validation.template_body_required'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function templateData(): array
    {
        $data = $this->validated('template');
        unset($data['id']);

        $data['channel_id'] = filled($data['channel_id'] ?? null) ? (int) $data['channel_id'] : null;
        $data['is_active'] = (bool) ($data['is_active'] ?? true);
        $data['is_system'] = (bool) ($data['is_system'] ?? false);

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    public function versionData(): array
    {
        return $this->validated('version');
    }
}
