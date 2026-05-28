<?php

namespace App\Http\Requests\Communication;

use App\Models\NotificationChannel;
use App\Rules\DictionaryCodeRule;
use App\Rules\TranslatedCommunicationFieldRequiredRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class NotificationChannelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAccess('communications.channels.manage') ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $channelId = $this->integer('channel.id') ?: null;

        return [
            'channel.id' => ['nullable', 'integer', Rule::exists(NotificationChannel::class, 'id')],
            'channel.code' => ['required', 'string', 'max:120', new DictionaryCodeRule, Rule::unique(NotificationChannel::class, 'code')->ignore($channelId)],
            'channel.name_translations' => ['required', 'array', new TranslatedCommunicationFieldRequiredRule],
            'channel.name_translations.*' => ['nullable', 'string', 'max:255'],
            'channel.description_translations' => ['nullable', 'array'],
            'channel.description_translations.*' => ['nullable', 'string', 'max:1000'],
            'channel.driver' => ['required', 'string', 'max:120'],
            'channel.provider' => ['nullable', 'string', 'max:120'],
            'channel.is_active' => ['nullable', 'boolean'],
            'channel.supports_internal' => ['nullable', 'boolean'],
            'channel.supports_external' => ['nullable', 'boolean'],
            'channel.supports_templates' => ['nullable', 'boolean'],
            'channel.supports_scheduling' => ['nullable', 'boolean'],
            'channel.supports_delivery_status' => ['nullable', 'boolean'],
            'channel.sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'channel.code.required' => tkey('communication.validation.channel_code_required'),
            'channel.code.max' => tkey('communication.validation.channel_code_invalid'),
            'channel.code.unique' => tkey('communication.validation.channel_code_unique'),
            'channel.name_translations.required' => tkey('communication.validation.default_translation_required'),
            'channel.driver.required' => tkey('communication.validation.channel_driver_required'),
            'channel.sort_order.integer' => tkey('communication.validation.sort_order_invalid'),
            'channel.sort_order.min' => tkey('communication.validation.sort_order_invalid'),
            'channel.sort_order.max' => tkey('communication.validation.sort_order_invalid'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function channelData(): array
    {
        $data = $this->validated('channel');
        unset($data['id']);

        foreach (['is_active', 'supports_internal', 'supports_external', 'supports_templates', 'supports_scheduling', 'supports_delivery_status'] as $field) {
            $data[$field] = (bool) ($data[$field] ?? false);
        }

        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        $data['provider'] = filled($data['provider'] ?? null) ? $data['provider'] : null;

        return $data;
    }

    public function channelId(): ?int
    {
        $id = $this->validated('channel.id', null);

        return filled($id) ? (int) $id : null;
    }
}
