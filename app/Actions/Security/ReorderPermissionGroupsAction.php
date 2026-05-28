<?php

namespace App\Actions\Security;

use App\Models\PermissionGroup;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReorderPermissionGroupsAction
{
    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    public function handle(array $items, ?User $actor = null, ?Request $request = null): void
    {
        DB::transaction(function () use ($items, $actor, $request): void {
            foreach ($items as $position => $item) {
                $group = $this->group($item);

                if ($group === null) {
                    continue;
                }

                $group->forceFill([
                    'sort_order' => (int) ($item['sort_order'] ?? (($position + 1) * 10)),
                ])->save();
            }

            app(RecordAuditLogAction::class)->handle(
                'permission_groups.reordered',
                $actor,
                null,
                [],
                [],
                ['count' => count($items)],
                $request,
            );
        });
    }

    /**
     * @param  array<string, mixed>  $item
     */
    private function group(array $item): ?PermissionGroup
    {
        return PermissionGroup::query()
            ->when(isset($item['id']), fn ($query) => $query->whereKey((int) $item['id']))
            ->when(! isset($item['id']) && isset($item['code']), fn ($query) => $query->where('code', (string) $item['code']))
            ->first();
    }
}
