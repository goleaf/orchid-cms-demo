<?php

namespace App\Http\Requests\Security;

use App\Models\PermissionRegistryItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReorderPermissionRegistryItemsRequest extends FormRequest
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
            'items' => ['required', 'array'],
            'items.*.id' => ['required_without:items.*.code', 'integer', Rule::exists(PermissionRegistryItem::class, 'id')],
            'items.*.code' => ['required_without:items.*.id', 'string', 'max:180'],
            'items.*.sort_order' => ['required', 'integer', 'min:0'],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function reorderData(): array
    {
        return $this->validated('items');
    }
}
