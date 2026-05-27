<?php

namespace App\Http\Requests\Marketing;

use App\Rules\LeadMarketingAccessRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ExportLeadsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAccess('crm.leads.export') ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'include_marketing' => ['nullable', 'boolean', new LeadMarketingAccessRule($this->user())],
        ];
    }
}
