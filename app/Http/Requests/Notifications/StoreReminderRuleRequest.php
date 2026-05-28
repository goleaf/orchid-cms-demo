<?php

namespace App\Http\Requests\Notifications;

use App\Models\NotificationTemplate;
use App\Models\ReminderRule;
use App\Rules\PublishedNotificationTemplateRule;
use App\Rules\TranslatedCommunicationFieldRequiredRule;
use App\Rules\ValidReminderTriggerRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReminderRuleRequest extends FormRequest
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
        $ruleId = $this->integer('rule.id') ?: null;

        return [
            'rule.id' => ['nullable', 'integer', Rule::exists(ReminderRule::class, 'id')],
            'rule.code' => ['required', 'string', 'max:120', Rule::unique(ReminderRule::class, 'code')->ignore($ruleId)],
            'rule.name_translations' => ['required', 'array', new TranslatedCommunicationFieldRequiredRule('notifications.validation.default_translation_required')],
            'rule.name_translations.*' => ['nullable', 'string', 'max:255'],
            'rule.trigger_type' => ['required', 'string', new ValidReminderTriggerRule],
            'rule.target_type' => ['required', 'string', 'max:120'],
            'rule.template_id' => ['required', 'integer', Rule::exists(NotificationTemplate::class, 'id'), new PublishedNotificationTemplateRule],
            'rule.offset_minutes' => ['required', 'integer', 'min:-525600', 'max:525600'],
            'rule.is_active' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'rule.code.required' => tkey('notifications.validation.reminder_rule_code_required'),
            'rule.code.unique' => tkey('notifications.validation.reminder_rule_code_unique'),
            'rule.name_translations.required' => tkey('notifications.validation.default_translation_required'),
            'rule.trigger_type.required' => tkey('notifications.validation.reminder_trigger_required'),
            'rule.target_type.required' => tkey('notifications.validation.target_type_required'),
            'rule.template_id.required' => tkey('notifications.validation.template_required'),
            'rule.offset_minutes.required' => tkey('notifications.validation.offset_required'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function ruleData(): array
    {
        $data = $this->validated('rule');
        unset($data['id']);

        $data['is_active'] = (bool) ($data['is_active'] ?? true);

        return $data;
    }
}
