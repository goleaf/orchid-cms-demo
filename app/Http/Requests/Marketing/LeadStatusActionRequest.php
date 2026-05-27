<?php

namespace App\Http\Requests\Marketing;

use Illuminate\Foundation\Http\FormRequest;

class LeadStatusActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyAccess(['crm.leads.change_status', 'crm.leads.update', 'platform.marketing.leads']) ?? false;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [];
    }
}
