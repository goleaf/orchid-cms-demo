<?php

namespace App\Actions\Security;

use App\Models\User;
use App\Rules\UniqueUserEmailRule;
use App\Rules\UserCanBeUpdatedRule;
use App\Rules\ValidUserLocaleRule;
use App\Rules\ValidUserTimezoneRule;
use App\Support\Security\UserLifecycle;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class UpdateUserAction
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function handle(User $user, array $attributes, ?User $actor = null, ?Request $request = null): User
    {
        $payload = $this->payload($attributes);

        Validator::make(['user' => $user->getKey()], [
            'user' => [new UserCanBeUpdatedRule($user, $actor, $payload)],
        ])->validate();

        Validator::make($payload, [
            'email' => ['sometimes', 'required', 'email', 'max:255', new UniqueUserEmailRule($user)],
            'preferred_locale' => ['nullable', 'string', 'max:12', new ValidUserLocaleRule],
            'timezone' => ['nullable', 'string', 'max:64', new ValidUserTimezoneRule],
        ])->validate();

        return DB::transaction(function () use ($user, $payload, $actor, $request): User {
            $lifecycle = app(UserLifecycle::class);
            $user->loadMissing(['roles', 'accessibleBranches', 'status']);
            $before = $lifecycle->userSnapshot($user);
            $beforeRoles = $user->roles->pluck('slug')->values()->all();

            $data = Arr::only($payload, [
                'name',
                'email',
                'preferred_locale',
                'timezone',
                'is_active',
                'security_lock_reason',
                'must_change_password',
            ]);

            if (array_key_exists('is_active', $data)) {
                $data['is_active'] = filter_var($data['is_active'], FILTER_VALIDATE_BOOLEAN);
            }

            if (array_key_exists('must_change_password', $data)) {
                if (Schema::hasColumn('users', 'must_change_password')) {
                    $data['must_change_password'] = filter_var($data['must_change_password'], FILTER_VALIDATE_BOOLEAN);
                } else {
                    unset($data['must_change_password']);
                }
            }

            if (filled($payload['password'] ?? null)) {
                $data['password'] = Hash::make((string) $payload['password']);

                if (Schema::hasColumn('users', 'password_changed_at')) {
                    $data['password_changed_at'] = now();
                }
            }

            if ($data !== []) {
                $user->fill($data)->save();
            }

            if (array_key_exists('status_id', $payload)) {
                app(ChangeUserStatusAction::class)->handle($user->refresh(), (int) $payload['status_id'], $actor, $request);
            }

            if (array_key_exists('roles', $payload)) {
                app(SyncUserRolesAction::class)->handle($user->refresh(), (array) $payload['roles'], $actor, $request);
            }

            if (array_key_exists('branch_ids', $payload) || array_key_exists('branch_access', $payload)) {
                app(SyncUserBranchAccessAction::class)->handle(
                    $user->refresh(),
                    (array) ($payload['branch_access'] ?? $payload['branch_ids'] ?? []),
                    $actor,
                    $request,
                );
            }

            if (array_key_exists('staff_profile', $payload)) {
                app(EnsureUserHasStaffProfileAction::class)->handle($user->refresh(), (array) $payload['staff_profile'], $actor, $request);
            }

            $user->refresh()->loadMissing(['roles', 'accessibleBranches', 'status', 'staffProfile']);

            app(RecordAuditLogAction::class)->handle(
                'user_updated',
                $actor,
                $user,
                $before,
                $lifecycle->userSnapshot($user),
                [
                    'old_role_slugs' => $beforeRoles,
                    'role_slugs' => $user->roles->pluck('slug')->values()->all(),
                    'branch_ids' => $user->accessibleBranches->pluck('id')->values()->all(),
                    'password_changed' => filled($payload['password'] ?? null),
                ],
                $request,
            );

            if (
                array_key_exists('roles', $payload)
                || array_key_exists('status_id', $payload)
                || array_key_exists('must_change_password', $payload)
            ) {
                app(RecordSecurityEventAction::class)->handle(
                    'user_critical_fields_updated',
                    $user,
                    'warning',
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
