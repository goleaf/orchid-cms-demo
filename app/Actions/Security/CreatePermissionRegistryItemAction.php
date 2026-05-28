<?php

namespace App\Actions\Security;

use App\Models\PermissionRegistryItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class CreatePermissionRegistryItemAction
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function handle(array $attributes, ?User $actor = null, ?Request $request = null): PermissionRegistryItem
    {
        return DB::transaction(function () use ($attributes, $actor, $request): PermissionRegistryItem {
            $item = PermissionRegistryItem::query()->create($this->data($attributes));

            app(RecordAuditLogAction::class)->handle(
                'permission_registry_item.created',
                $actor,
                $item,
                [],
                $item->only(['code', 'module', 'risk_level', 'is_active', 'is_system', 'permission_group_id']),
                [],
                $request,
            );

            return $item->refresh()->load('group');
        });
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

        $data['risk_level'] = (string) ($data['risk_level'] ?? PermissionRegistryItem::RISK_NORMAL);
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        $data['is_active'] = (bool) ($data['is_active'] ?? true);
        $data['is_system'] = (bool) ($data['is_system'] ?? true);

        return $data;
    }
}
