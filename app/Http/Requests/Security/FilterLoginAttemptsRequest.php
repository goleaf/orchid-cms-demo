<?php

namespace App\Http\Requests\Security;

use App\Models\User;
use App\Rules\LoginAttemptFailureReasonRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FilterLoginAttemptsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAccess('security.login_attempts.view') ?? false;
    }

    /**
     * @return array<string, array<int, mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['nullable', 'integer', Rule::exists(User::class, 'id')],
            'email' => ['nullable', 'email', 'max:255'],
            'guard' => ['nullable', 'string', 'max:64'],
            'ip_address' => ['nullable', 'ip'],
            'successful' => ['nullable', 'boolean'],
            'failure_reason' => ['nullable', 'string', new LoginAttemptFailureReasonRule],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.email' => tkey('security.validation.user_email_invalid'),
            'ip_address.ip' => tkey('security.validation.ip_address_invalid'),
            'failure_reason.string' => tkey('security.validation.invalid_login_failure_reason'),
            'date_to.after_or_equal' => tkey('security.validation.date_range_invalid'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'user_id' => tkey('security.login_attempts.fields.user'),
            'email' => tkey('security.login_attempts.fields.email'),
            'guard' => tkey('security.login_attempts.fields.guard'),
            'ip_address' => tkey('security.login_attempts.fields.ip_address'),
            'successful' => tkey('security.login_attempts.fields.successful'),
            'failure_reason' => tkey('security.login_attempts.fields.failure_reason'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function filters(): array
    {
        return $this->validated();
    }
}
