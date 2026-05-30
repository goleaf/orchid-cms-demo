<?php

namespace App\Http\Requests\Security;

use App\Rules\StaffPhoneRule;
use App\Rules\StaffWorkEmailRule;
use App\Rules\ValidUserLocaleRule;
use App\Rules\ValidUserTimezoneRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyAccess(['security.users.update_profile', 'platform.profile', 'platform.systems.users']) ?? false;
    }

    public function rules(): array
    {
        return [
            'user.name' => ['sometimes', 'required', 'string', 'max:255'],
            'user.preferred_locale' => ['nullable', 'string', 'max:12', new ValidUserLocaleRule],
            'user.timezone' => ['nullable', 'string', 'max:64', new ValidUserTimezoneRule],
            'user.display_name_translations' => ['nullable', 'array'],
            'user.display_name_translations.*' => ['nullable', 'string', 'max:255'],
            'user.phone' => ['nullable', 'string', 'max:64', new StaffPhoneRule],
            'user.work_email' => ['nullable', 'string', 'max:255', new StaffWorkEmailRule($this->user()?->staffProfile)],
            'user.avatar' => ['nullable', 'string', 'max:255'],
        ];
    }
}
