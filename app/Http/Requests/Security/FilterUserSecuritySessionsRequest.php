<?php

namespace App\Http\Requests\Security;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FilterUserSecuritySessionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAccess('security.sessions.view') ?? false;
    }

    /**
     * @return array<string, array<int, mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['nullable', 'integer', Rule::exists(User::class, 'id')],
            'guard' => ['nullable', 'string', 'max:64'],
            'ip_address' => ['nullable', 'ip'],
            'status' => ['nullable', 'string', Rule::in(['active', 'revoked', 'logged_out', 'current'])],
            'recently_active_minutes' => ['nullable', 'integer', 'min:1', 'max:10080'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'ip_address.ip' => tkey('security.validation.ip_address_invalid'),
            'status.in' => tkey('security.validation.session_status_invalid'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'user_id' => tkey('security.sessions.fields.user'),
            'guard' => tkey('security.sessions.fields.guard'),
            'ip_address' => tkey('security.sessions.fields.ip_address'),
            'status' => tkey('security.sessions.fields.status'),
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
