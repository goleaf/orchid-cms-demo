<?php

namespace App\Actions\Security;

use App\Models\User;
use Illuminate\Support\Facades\Schema;

class AssignUserBranchAccessAction
{
    /**
     * @param  array<int, mixed>|null  $branchIds
     */
    public function handle(User $user, ?array $branchIds, ?User $actor = null): User
    {
        if ($branchIds === null || ! Schema::hasTable('user_branch_access')) {
            return $user;
        }

        $sync = collect($branchIds)
            ->filter(fn (mixed $id): bool => is_numeric($id))
            ->map(fn (mixed $id): int => (int) $id)
            ->unique()
            ->mapWithKeys(fn (int $id): array => [
                $id => [
                    'access_level' => 'staff',
                    'updated_by_id' => $actor?->getKey(),
                    'created_by_id' => $actor?->getKey(),
                ],
            ])
            ->all();

        $user->accessibleBranches()->sync($sync);

        return $user->refresh();
    }
}
