<?php

namespace App\Http\Requests\Security;

use App\Models\PermissionGroup;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReorderPermissionGroupsRequest extends FormRequest
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
            'groups' => ['required', 'array'],
            'groups.*.id' => ['required_without:groups.*.code', 'integer', Rule::exists(PermissionGroup::class, 'id')],
            'groups.*.code' => ['required_without:groups.*.id', 'string', 'max:120'],
            'groups.*.sort_order' => ['required', 'integer', 'min:0'],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function reorderData(): array
    {
        return $this->validated('groups');
    }
}
