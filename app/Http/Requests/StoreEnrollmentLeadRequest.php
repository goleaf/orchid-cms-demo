<?php

namespace App\Http\Requests;

use App\Rules\AcceptedPrivacyConsent;
use App\Rules\ActivePublicBranch;
use App\Rules\ActivePublicTrainingProgram;
use App\Rules\PublicTrainingGroup;
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
            'training_program_id' => ['required', 'integer', new ActivePublicTrainingProgram],
            'branch_id' => ['required', 'integer', new ActivePublicBranch],
            'training_group_id' => [
                'nullable',
                'integer',
                new PublicTrainingGroup($this->input('training_program_id'), $this->input('branch_id')),
            ],
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
            'privacy_consent' => ['required', new AcceptedPrivacyConsent],
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
            'referrer_url' => ['nullable', 'url', 'max:255'],
            'landing_page' => ['nullable', 'string', 'max:255'],
            'form_page' => ['nullable', 'string', 'max:255'],
            'form_name' => ['nullable', 'string', 'max:120'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'training_program_id.required' => tkey('website.validation.course_required'),
            'branch_id.required' => tkey('website.validation.branch_required'),
            'first_name.required' => tkey('website.validation.first_name_required'),
            'email.required_without' => tkey('website.validation.contact_required'),
            'phone.required_without' => tkey('website.validation.contact_required'),
            'preferred_format.required' => tkey('website.validation.format_required'),
            'preferred_language.required' => tkey('website.validation.language_required'),
            'privacy_consent.required' => tkey('website.validation.privacy_consent'),
            'documents.max' => tkey('website.validation.documents_limit'),
            'documents.*.max' => tkey('website.validation.document_size'),
        ];
    }
}
