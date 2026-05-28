<?php

namespace App\Http\Requests;

use App\Enums\CourseFormat;
use App\Http\Requests\Concerns\HasWebsiteValidationAttributes;
use App\Rules\ConsentAcceptedRule;
use App\Rules\PhoneOrEmailRequiredRule;
use App\Rules\ValidLocaleRule;
use App\Rules\ValidPublicBranchRule;
use App\Rules\ValidPublicCourseRule;
use App\Rules\ValidPublicTrainingGroupRule;
use App\Services\LocaleManager;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\App;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class StoreWebsiteLeadRequest extends FormRequest
{
    use HasWebsiteValidationAttributes;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $locale = $this->input('locale');

        if (is_string($locale) && app(LocaleManager::class)->isActiveLocale($locale)) {
            App::setLocale($locale);
        }
    }

    protected function failedValidation(ValidatorContract $validator): void
    {
        if ($this->expectsJson()) {
            throw new HttpResponseException(response()->json([
                'message' => tkey('website.forms.messages.error'),
                'errors' => $validator->errors(),
            ], 422));
        }

        parent::failedValidation($validator);
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
            'preferred_format' => ['required', Rule::in(CourseFormat::values())],
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
            'course_id.integer' => tkey('website.validation.invalid_public_course'),
            'training_program_id.required_without' => tkey('website.validation.course_required'),
            'training_program_id.integer' => tkey('website.validation.invalid_public_course'),
            'branch_id.required' => tkey('website.validation.branch_required'),
            'branch_id.integer' => tkey('website.validation.invalid_public_branch'),
            'training_group_id.integer' => tkey('website.validation.invalid_public_group'),
            'full_name.required_without' => tkey('website.validation.first_name_required'),
            'full_name.max' => tkey('website.validation.name_too_long'),
            'first_name.required_without' => tkey('website.validation.first_name_required'),
            'first_name.max' => tkey('website.validation.name_too_long'),
            'last_name.max' => tkey('website.validation.name_too_long'),
            'email.required_without' => tkey('website.validation.contact_required'),
            'email.email' => tkey('website.validation.email_invalid'),
            'email.max' => tkey('website.validation.contact_too_long'),
            'phone.required_without' => tkey('website.validation.contact_required'),
            'phone.max' => tkey('website.validation.contact_too_long'),
            'messenger.max' => tkey('website.validation.contact_too_long'),
            'preferred_messenger.max' => tkey('website.validation.contact_too_long'),
            'city.max' => tkey('website.validation.text_too_long'),
            'preferred_format.required' => tkey('website.validation.format_required'),
            'preferred_format.in' => tkey('website.validation.format_invalid'),
            'preferred_language.required' => tkey('website.validation.language_required'),
            'preferred_language.max' => tkey('website.validation.language_invalid'),
            'preferred_time.max' => tkey('website.validation.text_too_long'),
            'budget_eur.numeric' => tkey('website.validation.budget_range'),
            'budget_eur.min' => tkey('website.validation.budget_range'),
            'budget_eur.max' => tkey('website.validation.budget_range'),
            'message.max' => tkey('website.validation.text_too_long'),
            'comment.max' => tkey('website.validation.text_too_long'),
            'privacy_consent.required_without' => tkey('website.validation.privacy_consent'),
            'consent_accepted.required_without' => tkey('website.validation.consent_required'),
            'documents.max' => tkey('website.validation.documents_limit'),
            'documents.*.file' => tkey('website.validation.document_invalid'),
            'documents.*.mimes' => tkey('website.validation.document_invalid'),
            'documents.*.extensions' => tkey('website.validation.document_invalid'),
            'documents.*.max' => tkey('website.validation.document_size'),
            'locale.max' => tkey('website.validation.invalid_locale'),
        ];
    }
}
