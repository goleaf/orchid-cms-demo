<?php

namespace App\Actions\Security;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Orchid\Platform\Models\Role;

class DeleteRoleAction
{
    public function handle(Role $role, ?User $actor = null, ?Request $request = null): void
    {
        if ($role->slug === 'superadmin') {
            throw ValidationException::withMessages([
                'role' => tkey('security.validation.superadmin_role_protected'),
            ]);
        }

        app(RecordAuditLogAction::class)->handle(
            'role.deleted',
            $actor,
            $role,
            $role->only(['name', 'slug', 'permissions']),
            [],
            [],
            $request,
        );

        $role->delete();
    }
}
