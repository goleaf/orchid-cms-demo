<?php

namespace App\Actions\Security;

use App\Models\PermissionGroup;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class UpdatePermissionGroupAction
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function handle(PermissionGroup|int|string $group, array $attributes, ?User $actor = null, ?Request $request = null): PermissionGroup
    {
        $model = $group instanceof PermissionGroup
            ? $group
            : PermissionGroup::query()->findOrFail($group);

        return DB::transaction(function () use ($model, $attributes, $actor, $request): PermissionGroup {
            $before = $model->only(['code', 'is_active', 'is_system', 'sort_order']);

            $model->fill($this->data($attributes))->save();

            app(RecordAuditLogAction::class)->handle(
                'permission_group.updated',
                $actor,
                $model,
                $before,
                $model->only(['code', 'is_active', 'is_system', 'sort_order']),
                [],
                $request,
            );

            return $model->refresh();
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
