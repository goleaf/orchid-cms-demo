<?php

namespace App\Actions\Security;

use App\Models\PermissionGroup;
use App\Models\PermissionRegistryItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class SyncPermissionRegistryAction
{
    /**
     * @return array<string, int>
     */
    public function handle(?User $actor = null, ?Request $request = null, bool $updateSafeLabels = true): array
    {
        $imported = app(ImportExistingOrchidPermissionsAction::class)->handle();

        if ($imported === []) {
            app(RecordAuditLogAction::class)->handle(
                'permission_registry.sync_skipped',
                $actor,
                null,
                [],
                [],
                ['reason' => 'no_permissions_discovered'],
                $request,
            );

            return [
                'discovered' => 0,
                'created' => 0,
                'updated' => 0,
                'skipped' => 0,
            ];
        }

        return DB::transaction(function () use ($imported, $actor, $request, $updateSafeLabels): array {
            $counts = [
                'discovered' => count($imported),
                'created' => 0,
                'updated' => 0,
                'skipped' => 0,
            ];

            foreach ($imported as $definition) {
                $group = $this->groupFor($definition['group_code'] ?? null);
                $item = PermissionRegistryItem::query()
                    ->where('code', $definition['code'])
                    ->first();

                if ($item === null) {
                    PermissionRegistryItem::query()->create([
                        'permission_group_id' => $group?->getKey(),
                        ...Arr::only($definition, [
                            'code',
                            'name_translations',
                            'description_translations',
                            'module',
                            'risk_level',
                            'is_active',
                            'is_system',
                            'sort_order',
                        ]),
                    ]);
                    $counts['created']++;

                    continue;
                }

                $updates = $this->safeUpdates($item, $definition, $group, $updateSafeLabels);

                if ($updates === []) {
                    $counts['skipped']++;

                    continue;
                }

                $item->fill($updates)->save();
                $counts['updated']++;
            }

            app(RecordAuditLogAction::class)->handle(
                'permission_registry.synced',
                $actor,
                null,
                [],
                [],
                $counts,
                $request,
            );

            return $counts;
        });
    }

    private function groupFor(?string $groupCode): ?PermissionGroup
    {
        if (blank($groupCode)) {
            return null;
        }

        return PermissionGroup::query()
            ->where('code', (string) $groupCode)
            ->first();
    }

    /**
     * @param  array<string, mixed>  $definition
     * @return array<string, mixed>
     */
    private function safeUpdates(PermissionRegistryItem $item, array $definition, ?PermissionGroup $group, bool $updateSafeLabels): array
    {
        $updates = [];

        if ($group !== null && (int) $item->permission_group_id !== (int) $group->getKey()) {
            $updates['permission_group_id'] = $group->getKey();
        }

        if (blank($item->module) && filled($definition['module'] ?? null)) {
            $updates['module'] = $definition['module'];
        }

        if ($item->risk_level === PermissionRegistryItem::RISK_NORMAL && ($definition['risk_level'] ?? null) !== PermissionRegistryItem::RISK_NORMAL) {
            $updates['risk_level'] = $definition['risk_level'];
        }

        if (! $item->is_system) {
            $updates['is_system'] = true;
        }

        if ($updateSafeLabels && $this->canUpdateLabels($item)) {
            $updates['name_translations'] = $definition['name_translations'] ?? null;
            $updates['description_translations'] = $definition['description_translations'] ?? null;
        }

        return $updates;
    }

    private function canUpdateLabels(PermissionRegistryItem $item): bool
    {
        $translations = $item->getTranslations('name');

        if ($translations === []) {
            return true;
        }

        $fallback = $item->displayName('en');

        return $fallback === $item->code
            || $fallback === str($item->code)->replace(['.', '_', '-'], ' ')->title()->toString();
    }
}
