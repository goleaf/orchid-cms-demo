<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class StoreEnrollmentLeadRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'training_program_id' => ['required', 'integer', 'exists:training_programs,id'],
            'branch_id' => ['required', 'integer', 'exists:branches,id'],
            'training_group_id' => ['nullable', 'integer', 'exists:training_groups,id'],
            'instructor_id' => ['nullable', 'integer', 'exists:instructors,id'],
            'first_name' => ['required', 'string', 'max:120'],
            'last_name' => ['nullable', 'string', 'max:120'],
            'email' => ['nullable', 'required_without:phone', 'email:rfc', 'max:190'],
            'phone' => ['nullable', 'required_without:email', 'string', 'max:60'],
            'messenger' => ['nullable', 'string', 'max:80'],
            'city' => ['nullable', 'string', 'max:120'],
            'preferred_format' => ['required', Rule::in(['offline', 'online', 'mixed'])],
            'preferred_language' => ['required', 'string', 'max:60'],
            'preferred_time' => ['nullable', 'string', 'max:120'],
            'budget_eur' => ['nullable', 'numeric', 'min:0', 'max:100000'],
            'source' => ['nullable', 'string', 'max:120'],
            'message' => ['nullable', 'string', 'max:2000'],
            'privacy_consent' => ['accepted'],
            'documents' => ['nullable', 'array', 'max:5'],
            'documents.*' => [
                'file',
                File::types(['pdf', 'jpg', 'jpeg', 'png'])
                    ->max(5 * 1024),
            ],
            'utm_source' => ['nullable', 'string', 'max:120'],
            'utm_medium' => ['nullable', 'string', 'max:120'],
            'utm_campaign' => ['nullable', 'string', 'max:120'],
            'utm_term' => ['nullable', 'string', 'max:120'],
            'utm_content' => ['nullable', 'string', 'max:120'],
            'referrer_url' => ['nullable', 'url', 'max:2048'],
        ];
    }
}
