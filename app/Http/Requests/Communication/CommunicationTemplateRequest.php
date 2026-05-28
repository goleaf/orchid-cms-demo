<?php

namespace App\Http\Requests\Communication;

use App\Models\CommunicationTemplate;
use App\Models\NotificationChannel;
use App\Rules\ActiveNotificationChannelRule;
use App\Rules\TranslatedCommunicationFieldRequiredRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CommunicationTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAccess('communications.templates.manage') ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $templateId = $this->integer('template.id') ?: null;

        return [
            'template.id' => ['nullable', 'integer', Rule::exists(CommunicationTemplate::class, 'id')],
            'template.code' => ['nullable', 'string', 'max:120', Rule::unique(CommunicationTemplate::class, 'code')->ignore($templateId)],
            'template.type' => ['required', 'string', Rule::in(CommunicationTemplate::typeValues())],
            'template.notification_channel_id' => ['nullable', 'integer', Rule::exists(NotificationChannel::class, 'id'), new ActiveNotificationChannelRule(mustSupportTemplates: true)],
            'template.name_translations' => ['required', 'array', new TranslatedCommunicationFieldRequiredRule],
            'template.name_translations.*' => ['nullable', 'string', 'max:255'],
            'template.subject_translations' => ['nullable', 'array'],
            'template.subject_translations.*' => ['nullable', 'string', 'max:255'],
            'template.body_translations' => ['required', 'array', new TranslatedCommunicationFieldRequiredRule],
            'template.body_translations.*' => ['nullable', 'string', 'max:5000'],
            'template.variable_keys' => ['nullable', 'array'],
            'template.variable_keys.*' => ['nullable', 'string', 'max:80'],
            'template.is_active' => ['nullable', 'boolean'],
            'template.sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'template.code.unique' => tkey('communication.validation.template_code_unique'),
            'template.type.required' => tkey('communication.validation.template_type_required'),
            'template.type.in' => tkey('communication.validation.template_type_invalid'),
            'template.name_translations.required' => tkey('communication.validation.default_translation_required'),
            'template.body_translations.required' => tkey('communication.validation.template_body_required'),
            'template.body_translations.*.max' => tkey('communication.validation.template_body_too_long'),
            'template.sort_order.integer' => tkey('communication.validation.sort_order_invalid'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function templateData(): array
    {
        $data = $this->validated('template');
        unset($data['id']);

        $data['notification_channel_id'] = filled($data['notification_channel_id'] ?? null)
            ? (int) $data['notification_channel_id']
            : null;
        $data['code'] = filled($data['code'] ?? null) ? $data['code'] : null;
        $data['is_active'] = (bool) ($data['is_active'] ?? false);
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        $data['variable_keys'] = collect($data['variable_keys'] ?? [])
            ->filter(fn (mixed $value): bool => filled($value))
            ->values()
            ->all();

        return $data;
    }

    public function templateId(): ?int
    {
        $id = $this->validated('template.id', null);

        return filled($id) ? (int) $id : null;
    }
}
