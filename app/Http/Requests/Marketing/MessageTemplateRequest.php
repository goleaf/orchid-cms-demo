<?php

namespace App\Http\Requests\Marketing;

use App\Models\MarketingMessageTemplate;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MessageTemplateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->hasAccess('platform.marketing.templates') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'template.name' => ['required', 'string', 'max:190'],
            'template.channel' => ['nullable', 'string', Rule::in(MarketingMessageTemplate::channelValues())],
            'template.subject' => ['nullable', 'string', 'max:190'],
            'template.body' => ['required', 'string', 'max:5000'],
            'template.is_active' => ['nullable', 'boolean'],
            'template.sort_order' => ['required', 'integer', 'min:0', 'max:9999'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'template.name.required' => tkey('crm.validation.message_template_name_required'),
            'template.name.max' => tkey('crm.validation.message_template_name_too_long'),
            'template.channel.in' => tkey('crm.validation.message_template_channel_invalid'),
            'template.subject.max' => tkey('crm.validation.message_template_subject_too_long'),
            'template.body.required' => tkey('crm.validation.message_template_body_required'),
            'template.body.max' => tkey('crm.validation.message_template_body_too_long'),
            'template.sort_order.required' => tkey('crm.validation.message_template_sort_order_required'),
            'template.sort_order.integer' => tkey('crm.validation.message_template_sort_order_invalid'),
            'template.sort_order.min' => tkey('crm.validation.message_template_sort_order_invalid'),
            'template.sort_order.max' => tkey('crm.validation.message_template_sort_order_invalid'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function templateData(): array
    {
        $data = $this->validated('template');

        return [
            ...$data,
            'channel' => filled($data['channel'] ?? null) ? $data['channel'] : null,
            'subject' => $data['subject'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? false),
            'sort_order' => (int) $data['sort_order'],
        ];
    }
}
