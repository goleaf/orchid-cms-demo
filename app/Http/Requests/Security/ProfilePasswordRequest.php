<?php

namespace App\Http\Requests\Security;

use App\Rules\PasswordPolicyRule;
use Illuminate\Foundation\Http\FormRequest;

class ProfilePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, array<int, mixed>|string>
     */
    public function rules(): array
    {
        $guard = config('platform.guard', 'web');

        return [
            'old_password' => 'required|current_password:'.$guard,
            'password' => ['required', 'confirmed', 'different:old_password', new PasswordPolicyRule],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'old_password.required' => tkey('security.validation.current_password_required'),
            'old_password.current_password' => tkey('security.validation.current_password_invalid'),
            'password.required' => tkey('security.validation.user_password_required'),
            'password.confirmed' => tkey('security.validation.password_confirmed'),
            'password.different' => tkey('security.validation.password_different'),
        ];
    }
}
