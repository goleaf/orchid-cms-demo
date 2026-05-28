<?php

namespace App\Actions\Security;

use App\Models\PermissionGroup;
use App\Models\PermissionRegistryItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AssignPermissionToGroupAction
{
    public function handle(
        PermissionRegistryItem|int|string $permission,
        PermissionGroup|int|string|null $group,
        ?User $actor = null,
        ?Request $request = null,
    ): PermissionRegistryItem {
        $item = $this->permission($permission);
        $targetGroup = $this->group($group);

        if ($item === null) {
            throw ValidationException::withMessages([
                'permission' => tkey('security.validation.permission_code_not_found'),
            ]);
        }

        return DB::transaction(function () use ($item, $targetGroup, $actor, $request): PermissionRegistryItem {
            $before = $item->only(['permission_group_id']);
            $item->forceFill(['permission_group_id' => $targetGroup?->getKey()])->save();

            app(RecordAuditLogAction::class)->handle(
                'permission_registry_item.group_assigned',
                $actor,
                $item,
                $before,
                ['permission_group_id' => $item->permission_group_id],
                [],
                $request,
            );

            return $item->refresh()->load('group');
        });
    }

    private function permission(PermissionRegistryItem|int|string $permission): ?PermissionRegistryItem
    {
        if ($permission instanceof PermissionRegistryItem) {
            return $permission;
        }

        return PermissionRegistryItem::query()
            ->when(is_numeric($permission), fn ($query) => $query->whereKey((int) $permission))
            ->when(! is_numeric($permission), fn ($query) => $query->where('code', (string) $permission))
            ->first();
    }

    private function group(PermissionGroup|int|string|null $group): ?PermissionGroup
    {
        if ($group === null || $group === '') {
            return null;
        }

        if ($group instanceof PermissionGroup) {
            return $group;
        }

        return PermissionGroup::query()
            ->when(is_numeric($group), fn ($query) => $query->whereKey((int) $group))
            ->when(! is_numeric($group), fn ($query) => $query->where('code', (string) $group))
            ->firstOrFail();
    }
}
