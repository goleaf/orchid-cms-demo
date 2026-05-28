<?php

namespace App\Rules;

use App\Models\PermissionRegistryItem;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;

class PermissionRegistryItemCanBeChangedRule implements DataAwareRule, ValidationRule
{
    /**
     * @var array<string, mixed>
     */
    private array $data = [];

    public function __construct(
        private readonly ?PermissionRegistryItem $item = null,
        private readonly string $root = 'item',
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $this->item?->exists || ! $this->item->is_system) {
            return;
        }

        $isSystem = data_get($this->data, $this->root.'.is_system', true);

        if (! filter_var($isSystem, FILTER_VALIDATE_BOOLEAN)) {
            $fail(tkey('security.validation.permission_registry_item_cannot_be_changed'));

            return;
        }

        $isActive = data_get($this->data, $this->root.'.is_active', true);

        if (! filter_var($isActive, FILTER_VALIDATE_BOOLEAN)) {
            $fail(tkey('security.validation.permission_registry_item_cannot_be_changed'));
        }
    }
}
