<?php

namespace App\Actions\Security;

use App\Models\PermissionGroup;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class CreatePermissionGroupAction
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function handle(array $attributes, ?User $actor = null, ?Request $request = null): PermissionGroup
    {
        return DB::transaction(function () use ($attributes, $actor, $request): PermissionGroup {
            $group = PermissionGroup::query()->create($this->data($attributes));

            app(RecordAuditLogAction::class)->handle(
                'permission_group.created',
                $actor,
                $group,
                [],
                $group->only(['code', 'is_active', 'is_system', 'sort_order']),
                [],
                $request,
            );

            return $group->refresh();
        });
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    private function data(array $attributes): array
    {
        $data = Arr::only($attributes, [
            'code',
            'name_translations',
            'description_translations',
            'icon',
            'color',
            'sort_order',
            'is_active',
            'is_system',
        ]);

        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        $data['is_active'] = (bool) ($data['is_active'] ?? true);
        $data['is_system'] = (bool) ($data['is_system'] ?? false);

        return $data;
    }
}
