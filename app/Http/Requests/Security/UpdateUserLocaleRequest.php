<?php

namespace App\Http\Requests\Security;

use App\Rules\ValidUserLocaleRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserLocaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyAccess(['security.users.update_profile', 'platform.profile', 'platform.systems.users']) ?? false;
    }

    public function rules(): array
    {
        return [
            'locale' => ['required', 'string', 'max:12', new ValidUserLocaleRule],
        ];
    }
}
