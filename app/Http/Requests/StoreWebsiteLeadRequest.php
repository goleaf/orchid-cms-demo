<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\HasWebsiteValidationAttributes;
use App\Rules\ConsentAcceptedRule;
use App\Rules\PhoneOrEmailRequiredRule;
use App\Rules\ValidLocaleRule;
use App\Rules\ValidPublicBranchRule;
use App\Rules\ValidPublicCourseRule;
use App\Rules\ValidPublicTrainingGroupRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class StoreWebsiteLeadRequest extends FormRequest
{
    use HasWebsiteValidationAttributes;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'course_id' => ['nullable', 'integer', new ValidPublicCourseRule],
            'training_program_id' => ['required_without:course_id', 'integer', new ValidPublicCourseRule],
            'course_category_id' => ['nullable', 'integer', 'exists:course_categories,id'],
            'branch_id' => ['required', 'integer', new ValidPublicBranchRule],
            'training_group_id' => ['nullable', 'integer', new ValidPublicTrainingGroupRule],
            'instructor_id' => ['nullable', 'integer', 'exists:instructors,id'],
            'full_name' => ['nullable', 'required_without:first_name', 'string', 'max:190'],
            'first_name' => ['nullable', 'required_without:full_name', 'string', 'max:120'],
            'last_name' => ['nullable', 'string', 'max:120'],
            'email' => ['nullable', 'required_without:phone', 'email:rfc', 'max:190', new PhoneOrEmailRequiredRule],
            'phone' => ['nullable', 'required_without:email', 'string', 'max:60'],
            'messenger' => ['nullable', 'string', 'max:80'],
            'preferred_messenger' => ['nullable', 'string', 'max:80'],
            'city' => ['nullable', 'string', 'max:120'],
            'preferred_format' => ['required', Rule::in(['offline', 'online', 'mixed', 'hybrid', 'individual', 'group'])],
            'preferred_language' => ['required', 'string', 'max:60'],
            'preferred_time' => ['nullable', 'string', 'max:120'],
            'budget_eur' => ['nullable', 'numeric', 'min:0', 'max:100000'],
            'source' => ['nullable', 'string', 'max:120'],
            'message' => ['nullable', 'string', 'max:2000'],
            'comment' => ['nullable', 'string', 'max:2000'],
            'privacy_consent' => ['nullable', 'required_without:consent_accepted', new ConsentAcceptedRule],
            'consent_accepted' => ['nullable', 'required_without:privacy_consent', new ConsentAcceptedRule],
            'consent_text_version' => ['nullable', 'string', 'max:120'],
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
            'referrer' => ['nullable', 'string', 'max:255'],
            'referrer_url' => ['nullable', 'url', 'max:255'],
            'landing_page' => ['nullable', 'string', 'max:255'],
            'form_page' => ['nullable', 'string', 'max:255'],
            'form_name' => ['nullable', 'string', 'max:120'],
            'locale' => ['nullable', 'string', 'max:12', new ValidLocaleRule],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'training_program_id.required_without' => tkey('website.validation.course_required'),
            'branch_id.required' => tkey('website.validation.branch_required'),
            'full_name.required_without' => tkey('website.validation.first_name_required'),
            'first_name.required_without' => tkey('website.validation.first_name_required'),
            'email.required_without' => tkey('website.validation.contact_required'),
            'phone.required_without' => tkey('website.validation.contact_required'),
            'preferred_format.required' => tkey('website.validation.format_required'),
            'preferred_language.required' => tkey('website.validation.language_required'),
            'privacy_consent.required_without' => tkey('website.validation.privacy_consent'),
            'consent_accepted.required_without' => tkey('website.validation.consent_required'),
            'documents.max' => tkey('website.validation.documents_limit'),
            'documents.*.max' => tkey('website.validation.document_size'),
        ];
    }
}
