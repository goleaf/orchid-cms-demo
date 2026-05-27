<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\HasWebsiteValidationAttributes;
use App\Rules\ConsentAcceptedRule;
use App\Rules\ValidLocaleRule;
use App\Rules\ValidPublicBranchRule;
use App\Rules\ValidPublicCourseRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreCallbackLeadRequest extends FormRequest
{
    use HasWebsiteValidationAttributes;

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
            'first_name' => ['required', 'string', 'max:120'],
            'last_name' => ['nullable', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:60'],
            'email' => ['nullable', 'email:rfc', 'max:190'],
            'messenger' => ['nullable', 'string', 'max:80'],
            'preferred_messenger' => ['nullable', 'string', 'max:80'],
            'city' => ['nullable', 'string', 'max:120'],
            'branch_id' => ['nullable', 'integer', new ValidPublicBranchRule],
            'course_id' => ['nullable', 'integer', new ValidPublicCourseRule],
            'training_program_id' => ['nullable', 'integer', new ValidPublicCourseRule],
            'course_category_id' => ['nullable', 'integer', 'exists:course_categories,id'],
            'preferred_language' => ['nullable', 'string', 'max:60'],
            'preferred_time' => ['nullable', 'string', 'max:120'],
            'source' => ['nullable', 'string', 'max:120'],
            'message' => ['nullable', 'string', 'max:2000'],
            'privacy_consent' => ['required', new ConsentAcceptedRule],
            'consent_text_version' => ['nullable', 'string', 'max:120'],
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
            'first_name.required' => tkey('website.validation.first_name_required'),
            'phone.required' => tkey('website.validation.phone_required'),
            'privacy_consent.required' => tkey('website.validation.privacy_consent'),
        ];
    }
}
