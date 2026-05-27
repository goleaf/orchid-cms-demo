<?php

namespace App\Http\Requests\Marketing;

use App\Models\MarketingLeadCommunication;
use App\Models\MarketingMessageTemplate;
use App\Rules\ActiveMessageTemplateForChannel;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class LeadCommunicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyAccess(['crm.leads.update', 'platform.marketing.leads', 'website.update_leads']) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'communication.channel' => ['required', 'string', 'max:60', Rule::in(MarketingMessageTemplate::channelValues())],
            'communication.template_id' => [
                'nullable',
                'integer',
                new ActiveMessageTemplateForChannel($this->input('communication.channel')),
            ],
            'communication.direction' => ['required', Rule::in(['inbound', 'outbound'])],
            'communication.subject' => ['nullable', 'string', 'max:190'],
            'communication.body' => [
                'nullable',
                'required_without_all:communication.template_id,communication.subject,communication.call_recording_url,communication.call_recording_reference',
                'string',
                'max:2000',
            ],
            'communication.client_replied' => ['nullable', 'boolean'],
            'communication.callback_required' => ['nullable', 'boolean'],
            'communication.callback_required_at' => ['nullable', 'date'],
            'communication.call_recording_url' => ['nullable', 'url', 'max:500'],
            'communication.call_recording_reference' => ['nullable', 'string', 'max:190'],
            'communication.call_result' => ['nullable', 'string', Rule::in(MarketingLeadCommunication::callResultValues())],
            'communication.duration_minutes' => ['nullable', 'integer', 'min:0', 'max:1440'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'communication.channel.required' => tkey('crm.validation.communication_channel_required'),
            'communication.direction.required' => tkey('crm.validation.communication_direction_required'),
            'communication.body.required_without_all' => tkey('crm.validation.communication_content_required'),
            'communication.call_recording_url.url' => tkey('crm.validation.call_recording_url_invalid'),
            'communication.duration_minutes.max' => tkey('crm.validation.call_duration_range'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function communicationData(): array
    {
        return $this->validated('communication');
    }

    public function template(): ?MarketingMessageTemplate
    {
        $payload = $this->communicationData();

        if (! filled($payload['template_id'] ?? null)) {
            return null;
        }

        return MarketingMessageTemplate::query()
            ->active()
            ->forChannel($payload['channel'])
            ->whereKey($payload['template_id'])
            ->firstOrFail();
    }

    public function callbackRequiredAt(): ?Carbon
    {
        $value = $this->validated('communication.callback_required_at', null);

        return filled($value) ? Carbon::parse($value) : null;
    }

    public function durationSeconds(): ?int
    {
        $value = $this->validated('communication.duration_minutes', null);

        return filled($value) ? ((int) $value) * 60 : null;
    }
}
