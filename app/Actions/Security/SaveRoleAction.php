<?php

namespace App\Actions\Security;

use App\Models\User;
use App\Support\Access\SuperadminPermissions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Orchid\Platform\Models\Role;

class SaveRoleAction
{
    public function handle(Role $role, Request $request, ?User $actor = null): Role
    {
        return DB::transaction(function () use ($role, $request, $actor): Role {
            $before = $role->exists ? $role->only(['name', 'slug', 'permissions']) : [];
            $payload = $request->input('role', []);

            if ($role->exists && $role->slug === 'superadmin' && ($payload['slug'] ?? 'superadmin') !== 'superadmin') {
                throw ValidationException::withMessages([
                    'role.slug' => tkey('security.validation.superadmin_role_protected'),
                ]);
            }

            $role->fill([
                'name' => (string) ($payload['name'] ?? $role->name),
                'slug' => (string) ($payload['slug'] ?? $role->slug),
            ]);

            $role->permissions = $this->decodePermissions($request->input('permissions', []));

            if ($role->slug === 'superadmin') {
                $role->name = 'Superadmin';
                $role->permissions = array_replace(
                    $role->permissions ?? [],
                    SuperadminPermissions::enabled(),
                );
            }

            $role->save();

            app(RecordAuditLogAction::class)->handle(
                $before === [] ? 'role.created' : 'role.updated',
                $actor,
                $role,
                $before,
                $role->only(['name', 'slug', 'permissions']),
                [],
                $request,
            );

            return $role;
        });
    }

    /**
     * @param  array<string, mixed>|null  $permissions
     * @return array<string, bool>
     */
    private function decodePermissions(?array $permissions): array
    {
        return collect($permissions ?? [])
            ->map(fn (mixed $value, string|int $key): array => [base64_decode((string) $key) => filter_var($value, FILTER_VALIDATE_BOOLEAN)])
            ->collapse()
            ->all();
    }
}
