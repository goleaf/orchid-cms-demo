<?php

namespace App\Actions\Security;

use App\Models\PermissionRegistryItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpdatePermissionRegistryItemAction
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function handle(PermissionRegistryItem|int|string $item, array $attributes, ?User $actor = null, ?Request $request = null): PermissionRegistryItem
    {
        $model = $item instanceof PermissionRegistryItem
            ? $item
            : PermissionRegistryItem::query()->findOrFail($item);

        $this->assertSystemPermissionNotWeakened($model, $attributes);

        return DB::transaction(function () use ($model, $attributes, $actor, $request): PermissionRegistryItem {
            $before = $model->only(['code', 'module', 'risk_level', 'is_active', 'is_system', 'permission_group_id']);

            $model->fill($this->data($attributes))->save();

            app(RecordAuditLogAction::class)->handle(
                'permission_registry_item.updated',
                $actor,
                $model,
                $before,
                $model->only(['code', 'module', 'risk_level', 'is_active', 'is_system', 'permission_group_id']),
                [],
                $request,
            );

            return $model->refresh()->load('group');
        });
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function assertSystemPermissionNotWeakened(PermissionRegistryItem $item, array $attributes): void
    {
        if (! $item->is_system) {
            return;
        }

        if (array_key_exists('code', $attributes) && (string) $attributes['code'] !== $item->code) {
            throw ValidationException::withMessages([
                'item.code' => tkey('security.validation.system_permission_code_protected'),
            ]);
        }

        if (array_key_exists('is_system', $attributes) && ! filter_var($attributes['is_system'], FILTER_VALIDATE_BOOLEAN)) {
            throw ValidationException::withMessages([
                'item.is_system' => tkey('security.validation.permission_registry_item_cannot_be_changed'),
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    private function data(array $attributes): array
    {
        $data = Arr::only($attributes, [
            'permission_group_id',
            'code',
            'name_translations',
            'description_translations',
            'module',
            'risk_level',
            'is_active',
            'is_system',
            'sort_order',
        ]);

        if (array_key_exists('sort_order', $data)) {
            $data['sort_order'] = (int) $data['sort_order'];
        }

        foreach (['is_active', 'is_system'] as $field) {
            if (array_key_exists($field, $data)) {
                $data[$field] = (bool) $data[$field];
            }
        }

        return $data;
    }
}
