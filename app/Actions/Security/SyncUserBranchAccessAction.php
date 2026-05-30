<?php

namespace App\Actions\Security;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class SyncUserBranchAccessAction
{
    /**
     * @param  array<int, mixed>|null  $branches
     */
    public function handle(User $user, ?array $branches, ?User $actor = null, ?Request $request = null): User
    {
        if ($branches === null || ! Schema::hasTable('user_branch_access')) {
            return $user;
        }

        $before = $user->accessibleBranches()->pluck('branches.id')->values()->all();
        $sync = collect($branches)
            ->mapWithKeys(function (mixed $value): array {
                if (is_array($value)) {
                    $branchId = $value['branch_id'] ?? $value['id'] ?? null;

                    if (! is_numeric($branchId)) {
                        return [];
                    }

                    return [(int) $branchId => ['access_level' => (string) ($value['access_level'] ?? 'staff')]];
                }

                return is_numeric($value) ? [(int) $value => ['access_level' => 'staff']] : [];
            })
            ->map(fn (array $pivot): array => [
                'access_level' => $pivot['access_level'] ?: 'staff',
                'created_by_id' => $actor?->getKey(),
                'updated_by_id' => $actor?->getKey(),
            ])
            ->all();

        $user->accessibleBranches()->sync($sync);
        $user->refresh()->load('accessibleBranches');

        app(RecordAuditLogAction::class)->handle(
            'user_branch_access_synced',
            $actor,
            $user,
            ['branch_ids' => $before],
            ['branch_ids' => $user->accessibleBranches->pluck('id')->values()->all()],
            [],
            $request,
        );

        return $user;
    }
}
