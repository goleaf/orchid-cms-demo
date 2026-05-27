<?php

namespace App\Http\Requests\Marketing;

use App\Models\MarketingMessageTemplate;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MessageTemplateDeleteRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $routeTemplate = $this->route('messageTemplate');
        $routeTemplateId = $routeTemplate instanceof MarketingMessageTemplate
            ? $routeTemplate->getKey()
            : $routeTemplate;

        $this->merge([
            'messageTemplate' => $routeTemplateId ?? $this->input('messageTemplate'),
        ]);
    }

    public function authorize(): bool
    {
        return $this->user()?->hasAccess('platform.marketing.templates') ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'messageTemplate' => ['required', 'integer', Rule::exists(MarketingMessageTemplate::class, 'id')],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'messageTemplate.required' => tkey('crm.validation.message_template_required'),
            'messageTemplate.exists' => tkey('crm.validation.message_template_not_found'),
        ];
    }

    public function templateId(): int
    {
        return (int) $this->validated('messageTemplate');
    }
}
