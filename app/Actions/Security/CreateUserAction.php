<?php

namespace App\Actions\Security;

use App\Models\StaffProfile;
use App\Models\User;
use App\Rules\UniqueUserEmailRule;
use App\Rules\ValidUserLocaleRule;
use App\Rules\ValidUserTimezoneRule;
use App\Support\Security\UserLifecycle;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CreateUserAction
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function handle(array $attributes, ?User $actor = null, ?Request $request = null): User
    {
        $payload = $this->payload($attributes);

        Validator::make($payload, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', new UniqueUserEmailRule],
            'preferred_locale' => ['nullable', 'string', 'max:12', new ValidUserLocaleRule],
            'timezone' => ['nullable', 'string', 'max:64', new ValidUserTimezoneRule],
        ])->validate();

        return DB::transaction(function () use ($payload, $actor, $request): User {
            $lifecycle = app(UserLifecycle::class);
            $status = blank($payload['status_id'] ?? null)
                ? $lifecycle->defaultStatus()
                : $lifecycle->status($payload['status_id']);

            $data = Arr::only($payload, [
                'name',
                'email',
                'preferred_locale',
                'timezone',
                'is_active',
                'must_change_password',
            ]);

            $data['is_active'] = filter_var($data['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN);

            if (Schema::hasColumn('users', 'status_id') && $status !== null) {
                $data['status_id'] = $status->getKey();
            }

            if (Schema::hasColumn('users', 'must_change_password')) {
                $data['must_change_password'] = filter_var($data['must_change_password'] ?? false, FILTER_VALIDATE_BOOLEAN);
            } else {
                unset($data['must_change_password']);
            }

            $password = filled($payload['password'] ?? null)
                ? (string) $payload['password']
                : Str::password(48);

            $data['password'] = Hash::make($password);

            if (Schema::hasColumn('users', 'password_changed_at') && filled($payload['password'] ?? null)) {
                $data['password_changed_at'] = now();
            }

            $user = User::query()->create($data);

            if (array_key_exists('roles', $payload)) {
                app(SyncUserRolesAction::class)->handle($user, (array) $payload['roles'], $actor, $request);
            }

            if (array_key_exists('branch_ids', $payload) || array_key_exists('branch_access', $payload)) {
                app(SyncUserBranchAccessAction::class)->handle(
                    $user,
                    (array) ($payload['branch_access'] ?? $payload['branch_ids'] ?? []),
                    $actor,
                    $request,
                );
            }

            if (Schema::hasTable('staff_profiles') && filled($payload['staff_profile'] ?? null)) {
                app(EnsureUserHasStaffProfileAction::class)->handle($user, (array) $payload['staff_profile'], $actor, $request);
            }

            $user->refresh()->loadMissing(['roles', 'accessibleBranches', 'status', 'staffProfile']);

            app(RecordAuditLogAction::class)->handle(
                'user_created',
                $actor,
                $user,
                [],
                $lifecycle->userSnapshot($user),
                [
                    'role_slugs' => $user->roles->pluck('slug')->values()->all(),
                    'branch_ids' => $user->accessibleBranches->pluck('id')->values()->all(),
                    'staff_profile_created' => $user->staffProfile instanceof StaffProfile,
                ],
                $request,
            );

            if ($user->roles->contains('slug', 'superadmin')) {
                app(RecordSecurityEventAction::class)->handle(
                    'user_high_risk_role_assigned',
                    $user,
                    'high',
                    ['actor_id' => $actor?->getKey()],
                    $request,
                );
            }

            return $user;
        });
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    private function payload(array $attributes): array
    {
        return (array) ($attributes['user'] ?? $attributes);
    }
}
