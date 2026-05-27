<?php

namespace App\Http\Requests;

use App\Rules\ConsentAcceptedRule;
use App\Rules\PhoneOrEmailRequiredRule;
use App\Rules\ValidLocaleRule;
use App\Rules\ValidPublicBranchRule;
use App\Rules\ValidPublicCourseRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreContactLeadRequest extends FormRequest
{
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
            'full_name' => ['nullable', 'required_without:first_name', 'string', 'max:190'],
            'first_name' => ['nullable', 'required_without:full_name', 'string', 'max:120'],
            'last_name' => ['nullable', 'string', 'max:120'],
            'email' => ['nullable', 'required_without:phone', 'email:rfc', 'max:190', new PhoneOrEmailRequiredRule],
            'phone' => ['nullable', 'required_without:email', 'string', 'max:60'],
            'messenger' => ['nullable', 'string', 'max:80'],
            'preferred_messenger' => ['nullable', 'string', 'max:80'],
            'branch_id' => ['nullable', 'integer', new ValidPublicBranchRule],
            'course_id' => ['nullable', 'integer', new ValidPublicCourseRule],
            'training_program_id' => ['nullable', 'integer', new ValidPublicCourseRule],
            'message' => ['nullable', 'string', 'max:2000'],
            'comment' => ['nullable', 'string', 'max:2000'],
            'privacy_consent' => ['required', new ConsentAcceptedRule],
            'source' => ['nullable', 'string', 'max:120'],
            'form_name' => ['nullable', 'string', 'max:120'],
            'locale' => ['nullable', 'string', 'max:12', new ValidLocaleRule],
            'utm_source' => ['nullable', 'string', 'max:120'],
            'utm_medium' => ['nullable', 'string', 'max:120'],
            'utm_campaign' => ['nullable', 'string', 'max:120'],
            'utm_term' => ['nullable', 'string', 'max:120'],
            'utm_content' => ['nullable', 'string', 'max:120'],
            'referrer' => ['nullable', 'string', 'max:255'],
            'referrer_url' => ['nullable', 'url', 'max:255'],
            'landing_page' => ['nullable', 'string', 'max:255'],
            'form_page' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'full_name.required_without' => tkey('website.validation.first_name_required'),
            'first_name.required_without' => tkey('website.validation.first_name_required'),
            'email.required_without' => tkey('website.validation.contact_required'),
            'phone.required_without' => tkey('website.validation.contact_required'),
            'privacy_consent.required' => tkey('website.validation.privacy_consent'),
        ];
    }
}
