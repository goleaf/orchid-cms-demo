<?php

namespace App\Http\Requests\Marketing;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class LeadCommentRequest extends FormRequest
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
            'comment.body' => ['required', 'string', 'max:2000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'comment.body.required' => tkey('crm.validation.comment_required'),
            'comment.body.max' => tkey('crm.validation.comment_too_long'),
        ];
    }

    public function body(): string
    {
        return (string) $this->validated('comment.body');
    }
}
