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
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:80'],
            'source' => ['nullable', 'string', 'max:80'],
            'manager_id' => ['nullable', 'integer'],
            'course_id' => ['nullable', 'integer'],
            'training_program_id' => ['nullable', 'integer'],
            'branch_id' => ['nullable', 'integer'],
            'training_group_id' => ['nullable', 'integer'],
            'tag_id' => ['nullable', 'integer'],
            'created_from' => ['nullable', 'date'],
            'created_to' => ['nullable', 'date'],
            'next_follow_up_from' => ['nullable', 'date'],
            'next_follow_up_to' => ['nullable', 'date'],
            'utm_source' => ['nullable', 'string', 'max:255'],
            'utm_campaign' => ['nullable', 'string', 'max:255'],
            'only_open' => ['nullable', 'boolean'],
            'only_closed' => ['nullable', 'boolean'],
            'only_converted' => ['nullable', 'boolean'],
        ];
    }
}
