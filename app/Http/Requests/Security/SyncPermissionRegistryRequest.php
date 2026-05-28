<?php

namespace App\Http\Requests\Security;

use App\Rules\PermissionCodeExistsRule;
use App\Rules\PermissionRegistryCodeRule;
use Illuminate\Foundation\Http\FormRequest;

class SyncPermissionRegistryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyAccess(['security.permissions.manage', 'platform.systems.roles']) ?? false;
    }

    /**
     * @return array<string, array<int, mixed>|string>
     */
    public function rules(): array
    {
        return [
            'sync.permissions' => ['nullable', 'array'],
            'sync.permissions.*' => ['required', 'string', 'max:180', new PermissionRegistryCodeRule, new PermissionCodeExistsRule],
            'sync.update_safe_labels' => ['nullable', 'boolean'],
            'sync.dry_run' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'sync.permissions.*.max' => tkey('security.validation.permission_registry_code_invalid'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function syncData(): array
    {
        $data = $this->validated('sync') ?? [];
        $data['update_safe_labels'] = (bool) ($data['update_safe_labels'] ?? true);
        $data['dry_run'] = (bool) ($data['dry_run'] ?? false);

        return $data;
    }
}
