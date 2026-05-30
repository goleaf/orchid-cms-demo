<?php

namespace App\Http\Requests\Security;

use App\Rules\ValidUserTimezoneRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserTimezoneRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyAccess(['security.users.update_profile', 'platform.profile', 'platform.systems.users']) ?? false;
    }

    public function rules(): array
    {
        return [
            'timezone' => ['required', 'string', 'max:64', new ValidUserTimezoneRule],
        ];
    }
}
