<?php

namespace App\Actions\Security;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;

class UpdateUserProfileAction
{
    public function handle(User $user, array $attributes, ?Request $request = null, ?User $actor = null): User
    {
        $actor ??= $request?->user();
        $before = $user->only(['name', 'preferred_locale', 'timezone']);

        $user->fill(Arr::only($attributes, ['name', 'preferred_locale', 'timezone']))->save();

        if (Schema::hasTable('staff_profiles') && $user->staffProfile()->exists()) {
            $profileData = Arr::only($attributes, [
                'display_name_translations',
                'phone',
                'work_email',
                'avatar',
            ]);

            if ($profileData !== []) {
                app(UpdateStaffProfileAction::class)->handle($user->staffProfile()->firstOrFail(), $profileData, $actor, $request);
            }
        }

        app(RecordAuditLogAction::class)->handle(
            'user_profile_updated',
            $actor,
            $user,
            $before,
            $user->only(['name', 'preferred_locale', 'timezone']),
            [],
            $request,
        );

        return $user->refresh();
    }
}
