<?php

namespace App\Http\Requests\Security;

use App\Http\Requests\Security\Concerns\ResolvesTargetUser;
use App\Rules\StaffPhoneRule;
use App\Rules\StaffProfileUserUniqueRule;
use App\Rules\StaffWorkEmailRule;
use Illuminate\Foundation\Http\FormRequest;

class EnsureUserHasStaffProfileRequest extends FormRequest
{
    use ResolvesTargetUser;

    public function authorize(): bool
    {
        return $this->user()?->hasAnyAccess(['security.staff_profiles.manage', 'security.users.update_profile', 'platform.systems.users']) ?? false;
    }

    public function rules(): array
    {
        return [
            'user_id' => [new StaffProfileUserUniqueRule],
            'profile' => ['nullable', 'array'],
            'profile.work_email' => ['nullable', 'string', 'max:255', new StaffWorkEmailRule],
            'profile.phone' => ['nullable', 'string', 'max:64', new StaffPhoneRule],
        ];
    }
}
