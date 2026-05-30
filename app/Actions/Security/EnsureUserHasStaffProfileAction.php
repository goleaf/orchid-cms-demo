<?php

namespace App\Actions\Security;

use App\Models\StaffProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class EnsureUserHasStaffProfileAction
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function handle(User $user, array $attributes = [], ?User $actor = null, ?Request $request = null): StaffProfile
    {
        if (! Schema::hasTable('staff_profiles')) {
            throw new RuntimeException('Staff profiles are not available in this project.');
        }

        $profile = StaffProfile::withTrashed()
            ->where('user_id', $user->getKey())
            ->first();

        if ($profile !== null) {
            if ($profile->trashed()) {
                $profile->restore();
            }

            if ($attributes !== []) {
                return app(UpdateStaffProfileAction::class)->handle($profile, $attributes, $actor, $request);
            }

            return $profile->refresh();
        }

        if (blank($attributes['staff_number'] ?? null)) {
            $attributes['staff_number'] = app(GenerateStaffNumberAction::class)->handle();
        }

        $profile = app(CreateStaffProfileAction::class)->handle($user, $attributes, $actor, $request);

        app(RecordAuditLogAction::class)->handle(
            'user_staff_profile_ensured',
            $actor,
            $user,
            [],
            ['staff_profile_id' => $profile->getKey()],
            [],
            $request,
        );

        return $profile;
    }
}
