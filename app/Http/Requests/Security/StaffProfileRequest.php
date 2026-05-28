<?php

namespace App\Http\Requests\Security;

use App\Models\Branch;
use App\Models\StaffProfile;
use App\Models\User;
use App\Rules\StaffNumberFormatRule;
use App\Rules\StaffProfileUserUniqueRule;
use App\Rules\ValidUserLocaleRule;
use App\Rules\ValidUserTimezoneRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StaffProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyAccess(['security.staff_profiles.manage', 'platform.systems.users']) ?? false;
    }

    /**
     * @return array<string, array<int, mixed>|string>
     */
    public function rules(): array
    {
        $profileId = $this->integer('profile.id') ?: null;

        return [
            'profile.id' => ['nullable', 'integer', Rule::exists(StaffProfile::class, 'id')],
            'profile.user_id' => ['required', 'integer', Rule::exists(User::class, 'id'), new StaffProfileUserUniqueRule($profileId)],
            'profile.staff_number' => [
                'nullable',
                'string',
                'max:32',
                new StaffNumberFormatRule,
                Rule::unique(StaffProfile::class, 'staff_number')->ignore($profileId),
            ],
            'profile.branch_id' => ['nullable', 'integer', Rule::exists(Branch::class, 'id')],
            'profile.display_name_translations' => ['nullable', 'array'],
            'profile.display_name_translations.*' => ['nullable', 'string', 'max:255'],
            'profile.job_title_translations' => ['nullable', 'array'],
            'profile.job_title_translations.*' => ['nullable', 'string', 'max:255'],
            'profile.public_bio_translations' => ['nullable', 'array'],
            'profile.public_bio_translations.*' => ['nullable', 'string', 'max:2000'],
            'profile.phone' => ['nullable', 'string', 'max:64'],
            'profile.work_email' => ['nullable', 'email', 'max:255'],
            'profile.preferred_locale' => ['nullable', 'string', 'max:12', new ValidUserLocaleRule],
            'profile.timezone' => ['nullable', 'string', 'max:64', new ValidUserTimezoneRule],
            'profile.avatar' => ['nullable', 'string', 'max:255'],
            'profile.is_visible_on_site' => ['nullable', 'boolean'],
            'profile.internal_notes' => ['nullable', 'string', 'max:5000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'profile.user_id.required' => tkey('security.validation.staff_profile_user_required'),
            'profile.user_id.exists' => tkey('security.validation.staff_profile_user_invalid'),
            'profile.staff_number.unique' => tkey('security.validation.staff_number_unique'),
            'profile.branch_id.exists' => tkey('security.validation.staff_profile_branch_invalid'),
            'profile.display_name_translations.*.max' => tkey('security.validation.staff_profile_name_too_long'),
            'profile.job_title_translations.*.max' => tkey('security.validation.staff_profile_job_title_too_long'),
            'profile.public_bio_translations.*.max' => tkey('security.validation.staff_profile_bio_too_long'),
            'profile.work_email.email' => tkey('security.validation.staff_profile_work_email_invalid'),
            'profile.work_email.max' => tkey('security.validation.staff_profile_work_email_invalid'),
            'profile.preferred_locale.max' => tkey('security.validation.user_locale_invalid'),
            'profile.timezone.max' => tkey('security.validation.user_timezone_invalid'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function profileData(): array
    {
        $data = $this->validated('profile');
        unset($data['id']);

        $data['is_visible_on_site'] = (bool) ($data['is_visible_on_site'] ?? false);

        return $data;
    }

    public function profileId(): ?int
    {
        $id = $this->validated('profile.id', null);

        return filled($id) ? (int) $id : null;
    }
}
